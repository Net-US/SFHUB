<?php

namespace App\Services;

use App\Models\IndodaxConnection;
use App\Models\InvestmentInstrument;
use App\Models\FinanceAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Indodax API Service
 * 
 * Dokumentasi: https://indodax.com/downloads/INDODAXCOM-API-DOCUMENTATION.pdf
 * 
 * Private API endpoint: https://indodax.com/tapi
 * Authentication: HMAC-SHA512 signature dengan API Key & Secret Key
 */
class IndodaxService
{
    private const API_URL = 'https://indodax.com/tapi';
    private const PUBLIC_API_URL = 'https://indodax.com/api';

    private IndodaxConnection $connection;
    private int $userId;

    public function __construct(IndodaxConnection $connection)
    {
        $this->connection = $connection;
        $this->userId = $connection->user_id;
    }

    /**
     * Call private API method dengan authentication
     */
    private function callPrivateApi(string $method, array $params = []): array
    {
        if (!$this->connection->isValid()) {
            throw new \Exception('Koneksi Indodax tidak valid. API Key atau Secret Key kosong.');
        }

        // Prepare POST data
        $params['method'] = $method;
        $params['timestamp'] = (int) (microtime(true) * 1000); // millisecond timestamp
        $params['recvWindow'] = 5000; // 5 detik window

        // Build query string
        $postData = http_build_query($params);

        // Generate HMAC-SHA512 signature
        $sign = hash_hmac('sha512', $postData, $this->connection->api_secret);

        try {
            $response = Http::withHeaders([
                'Key' => $this->connection->api_key,
                'Sign' => $sign,
            ])->asForm()->post(self::API_URL, $params);

            $data = $response->json();

            if (!$response->successful() || !isset($data['success']) || $data['success'] != 1) {
                $errorMsg = $data['error'] ?? 'Unknown error from Indodax API';
                throw new \Exception($errorMsg);
            }

            return $data['return'] ?? [];

        } catch (\Exception $e) {
            Log::error('Indodax API Error', [
                'user_id' => $this->userId,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get user info: balances & server time
     * Method: getInfo
     */
    public function getInfo(): array
    {
        return $this->callPrivateApi('getInfo');
    }

    /**
     * Get transaction history: deposits & withdrawals
     * Method: transHistory
     */
    public function getTransactionHistory(): array
    {
        return $this->callPrivateApi('transHistory');
    }

    /**
     * Get trade history
     * Method: tradeHistory
     */
    public function getTradeHistory(string $pair = 'btc_idr', ?int $count = null, ?int $fromId = null): array
    {
        $params = ['pair' => $pair];
        if ($count) $params['count'] = $count;
        if ($fromId) $params['from_id'] = $fromId;
        
        return $this->callPrivateApi('tradeHistory', $params);
    }

    /**
     * Sync balances dari Indodax ke database
     * Update/create InvestmentInstrument untuk setiap crypto yang ada balance
     */
    public function syncBalances(): array
    {
        try {
            $info = $this->getInfo();
            $balances = $info['balance'] ?? [];

            // Cari atau buat FinanceAccount untuk Indodax
            $financeAccount = FinanceAccount::firstOrCreate(
                [
                    'user_id' => $this->userId,
                    'type' => 'investment',
                    'name' => 'Indodax',
                ],
                [
                    'account_number' => 'INDODAX-' . substr($this->connection->api_key, 0, 8),
                    'balance' => 0,
                    'currency' => 'IDR',
                    'color' => '#f97316',
                    'icon' => 'bitcoin',
                    'notes' => 'Auto-synced from Indodax API',
                    'is_active' => true,
                ]
            );

            $syncedCoins = [];
            $totalValueIdr = 0;

            // Fetch current prices dari public API
            $tickers = $this->getPublicTickers();

            foreach ($balances as $coin => $balance) {
                $balance = (float) $balance;
                
                // Skip jika balance = 0
                if ($balance <= 0) continue;

                // Skip IDR (fiat)
                if (strtolower($coin) === 'idr') {
                    $totalValueIdr += $balance;
                    continue;
                }

                $symbol = strtoupper($coin);
                $pair = strtolower($coin) . '_idr';
                
                // Get current price
                $currentPrice = 0;
                if (isset($tickers[$pair]['last'])) {
                    $currentPrice = (float) $tickers[$pair]['last'];
                }

                $currentValueIdr = $balance * $currentPrice;
                $totalValueIdr += $currentValueIdr;

                // Update atau create instrument
                $instrument = InvestmentInstrument::updateOrCreate(
                    [
                        'user_id' => $this->userId,
                        'finance_account_id' => $financeAccount->id,
                        'symbol' => $symbol,
                        'type' => 'crypto',
                    ],
                    [
                        'name' => $this->getCoinName($symbol),
                        'current_price' => $currentPrice,
                        'total_quantity' => $balance,
                        'notes' => 'Auto-synced from Indodax on ' . now()->format('Y-m-d H:i'),
                    ]
                );

                $syncedCoins[] = [
                    'symbol' => $symbol,
                    'balance' => $balance,
                    'price' => $currentPrice,
                    'value_idr' => $currentValueIdr,
                ];
            }

            // Update total balance di FinanceAccount
            $financeAccount->update(['balance' => $totalValueIdr]);

            $this->connection->updateSyncStatus(true, 'Synced ' . count($syncedCoins) . ' coins successfully');

            return [
                'success' => true,
                'synced_coins' => $syncedCoins,
                'total_value_idr' => $totalValueIdr,
                'synced_at' => now()->toIso8601String(),
            ];

        } catch (\Exception $e) {
            $this->connection->updateSyncStatus(false, $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get public tickers (semua pair)
     */
    private function getPublicTickers(): array
    {
        try {
            $response = Http::get(self::PUBLIC_API_URL . '/tickers');
            $data = $response->json();
            return $data['tickers'] ?? [];
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Indodax public tickers', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Helper: get coin full name
     */
    private function getCoinName(string $symbol): string
    {
        $names = [
            'BTC' => 'Bitcoin',
            'ETH' => 'Ethereum',
            'USDT' => 'Tether',
            'BNB' => 'Binance Coin',
            'XRP' => 'Ripple',
            'ADA' => 'Cardano',
            'DOGE' => 'Dogecoin',
            'SOL' => 'Solana',
            'TRX' => 'Tron',
            'LTC' => 'Litecoin',
            'MATIC' => 'Polygon',
            'DOT' => 'Polkadot',
            'SHIB' => 'Shiba Inu',
            'AVAX' => 'Avalanche',
            'UNI' => 'Uniswap',
            'LINK' => 'Chainlink',
            'XLM' => 'Stellar',
            'ATOM' => 'Cosmos',
            'ETC' => 'Ethereum Classic',
            'BCH' => 'Bitcoin Cash',
        ];

        return $names[$symbol] ?? $symbol;
    }

    /**
     * Test koneksi API (cek apakah API key valid)
     */
    public function testConnection(): array
    {
        try {
            $info = $this->getInfo();
            return [
                'success' => true,
                'message' => 'Koneksi berhasil! API Key valid.',
                'server_time' => $info['server_time'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
            ];
        }
    }
}
