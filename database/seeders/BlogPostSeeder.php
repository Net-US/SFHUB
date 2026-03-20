<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run()
    {
        // Create categories first
        $categories = [
            ['name' => 'Web Development', 'slug' => 'web-development', 'description' => 'Tips dan tutorial tentang pengembangan web'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'UI/UX design dan grafis'],
            ['name' => 'Programming', 'slug' => 'programming', 'description' => 'Berbagai bahasa pemrograman dan algoritma'],
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Berita dan tren teknologi terkini'],
            ['name' => 'Tutorial', 'slug' => 'tutorial', 'description' => 'Tutorial langkah demi langkah'],
        ];

        foreach ($categories as $cat) {
            BlogCategory::firstOrCreate($cat);
        }

        // Create tags
        $tags = [
            'HTML',
            'CSS',
            'JavaScript',
            'PHP',
            'Laravel',
            'React',
            'Vue',
            'Tailwind',
            'UI Design',
            'UX',
            'Frontend',
            'Backend',
            'Database',
            'API',
            'Tutorial',
            'Tips',
            'Trick',
            'Best Practice',
            'Web Design',
            'Mobile',
            'Responsive'
        ];

        foreach ($tags as $tagName) {
            BlogTag::firstOrCreate([
                'name' => $tagName,
                'slug' => Str::slug($tagName)
            ]);
        }

        // Sample posts
        $posts = [
            [
                'title' => 'Panduan Lengkap Belajar Laravel untuk Pemula',
                'slug' => 'panduan-lengkap-belajar-laravel-untuk-pemula',
                'excerpt' => 'Tutorial lengkap untuk memulai belajar framework Laravel dari dasar hingga mahir.',
                'content' => '<h2>Apa itu Laravel?</h2><p>Laravel adalah framework PHP yang populer dan powerful untuk mengembangkan aplikasi web modern. Framework ini menyediakan struktur yang elegan dan tools yang membantu developer membangun aplikasi dengan cepat dan efisien.</p><h3>Kenapa Memilih Laravel?</h3><ul><li><strong>Syntax Elegan:</strong> Laravel memiliki syntax yang bersih dan mudah dibaca</li><li><strong>Eloquent ORM:</strong> Database query yang intuitive dan powerful</li><li><strong>Routing System:</strong> URL routing yang fleksibel</li><li><strong>Template Engine:</strong> Blade template yang powerful</li></ul><p>Dalam tutorial ini, kita akan mempelajari dasar-dasar Laravel step by step...</p>',
                'meta_title' => 'Panduan Lengkap Belajar Laravel untuk Pemula | SFHUB',
                'meta_description' => 'Tutorial lengkap untuk memulai belajar framework Laravel dari dasar hingga mahir. Cocok untuk pemula yang ingin belajar web development.',
                'status' => 'published',
                'categories' => ['Web Development', 'Programming'],
                'tags' => ['Laravel', 'PHP', 'Backend', 'Tutorial'],
                'published_at' => now()->subDays(5),
                'views' => 1250,
            ],
            [
                'title' => '10 Tips CSS untuk Membuat Website Lebih Menarik',
                'slug' => '10-tips-css-untuk-membuat-website-lebih-menarik',
                'excerpt' => 'Teknik CSS modern untuk meningkatkan visual design website Anda.',
                'content' => '<h2>Pengenalan CSS Modern</h2><p>CSS telah berkembang pesat dalam beberapa tahun terakhir. Dengan fitur-fitur baru seperti Grid, Flexbox, dan Custom Properties, kita bisa membuat website yang lebih menarik dan responsif.</p><h3>Tips #1: Gunakan CSS Grid untuk Layout</h3><p>CSS Grid adalah solusi sempurna untuk membuat layout kompleks dengan mudah. Berbeda dengan Flexbox yang lebih cocok untuk komponen satu dimensi, Grid dirancang khusus untuk layout dua dimensi.</p><pre><code>.container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}</code></pre><p>Dengan kode di atas, kita bisa membuat grid yang responsif secara otomatis...</p>',
                'meta_title' => '10 Tips CSS untuk Membuat Website Lebih Menarik',
                'meta_description' => 'Teknik CSS modern untuk meningkatkan visual design website Anda. Pelajari Grid, Flexbox, Animasi, dan lainnya.',
                'status' => 'published',
                'categories' => ['Design', 'Web Development'],
                'tags' => ['CSS', 'Frontend', 'Web Design', 'Tips'],
                'published_at' => now()->subDays(3),
                'views' => 890,
            ],
            [
                'title' => 'Membangun RESTful API dengan Laravel',
                'slug' => 'membangun-restful-api-dengan-laravel',
                'excerpt' => 'Tutorial langkah demi langkah membuat RESTful API menggunakan Laravel.',
                'content' => '<h2>Apa itu RESTful API?</h2><p>RESTful API adalah architectural style untuk designing networked applications. Laravel menyediakan tools yang powerful untuk membangun API dengan mudah.</p><h3>Step 1: Setup Project</h3><p>Pertama, buat project Laravel baru:</p><pre><code>composer create-project laravel/laravel api-project
cd api-project</code></pre><h3>Step 2: Buat API Routes</h3><p>Buka file routes/api.php dan tambahkan routes:</p><pre><code>Route::apiResource(\'posts\', PostController::class);</code></pre><p>Ini akan otomatis generate routes untuk index, store, show, update, dan delete...</p>',
                'meta_title' => 'Membangun RESTful API dengan Laravel | Tutorial API',
                'meta_description' => 'Tutorial lengkap membuat RESTful API menggunakan Laravel. Pelajari best practices dan implementasi yang efisien.',
                'status' => 'published',
                'featured' => false,
                'categories' => ['Web Development', 'Programming'],
                'tags' => ['Laravel', 'API', 'Backend', 'RESTful'],
                'published_at' => now()->subDays(2),
                'views' => 567,
            ],
            [
                'title' => 'Responsive Design dengan Tailwind CSS',
                'slug' => 'responsive-design-dengan-tailwind-css',
                'excerpt' => 'Cara mudah membuat website responsif menggunakan Tailwind CSS.',
                'content' => '<h2>Kenapa Tailwind CSS?</h2><p>Tailwind CSS adalah utility-first CSS framework yang memungkinkan kita membuat custom design tanpa menulis CSS. Framework ini sangat populer karena fleksibilitas dan produktivitas yang tinggi.</p><h3>Setup Tailwind CSS</h3><p>Install Tailwind CSS di project Anda:</p><pre><code>npm install -D tailwindcss
npx tailwindcss init</code></pre><h3>Responsive Breakpoints</h3><p>Tailwind menyediakan breakpoints yang mudah digunakan:</p><pre><code>&lt;div class="block sm:flex lg:hidden"&gt;
  &lt;!-- Content --&gt;
&lt;/div&gt;</code></pre><ul><li><strong>sm:</strong> 640px ke atas</li><li><strong>md:</strong> 768px ke atas</li><li><strong>lg:</strong> 1024px ke atas</li><li><strong>xl:</strong> 1280px ke atas</li></ul>',
                'meta_title' => 'Responsive Design dengan Tailwind CSS | Tutorial Lengkap',
                'meta_description' => 'Tutorial lengkap membuat website responsif menggunakan Tailwind CSS. Pelajari breakpoints, utilities, dan best practices.',
                'status' => 'published',
                'featured' => false,
                'categories' => ['Design', 'Web Development'],
                'tags' => ['Tailwind', 'CSS', 'Responsive', 'Frontend'],
                'published_at' => now()->subDays(1),
                'views' => 432,
            ],
            [
                'title' => 'Best Practice untuk Database Design',
                'slug' => 'best-practice-untuk-database-design',
                'excerpt' => 'Panduan lengkap merancang database yang efisien dan scalable.',
                'content' => '<h2>Fundamental Database Design</h2><p>Database design yang baik adalah fondasi dari aplikasi yang scalable. Berikut adalah best practices yang perlu dipertimbangkan.</p><h3>1. Normalization</h3><p>Normalization adalah proses mengorganisir data untuk mengurangi redundancy:</p><ul><li><strong>First Normal Form (1NF):</strong> Eliminasi repeating groups</li><li><strong>Second Normal Form (2NF):</strong> Eliminasi partial dependencies</li><li><strong>Third Normal Form (3NF):</strong> Eliminasi transitive dependencies</li></ul><h3>2. Indexing Strategy</h3><p>Index yang tepat bisa meningkatkan performa query secara drastis:</p><pre><code>-- Index untuk kolom yang sering diquery
CREATE INDEX idx_user_email ON users(email);

-- Composite index untuk multiple kolom
CREATE INDEX idx_post_status_date ON posts(status, created_at);</code></pre>',
                'meta_title' => 'Best Practice untuk Database Design | Panduan Lengkap',
                'meta_description' => 'Panduan lengkap merancang database yang efisien dan scalable. Pelajari normalization, indexing, dan optimization techniques.',
                'status' => 'draft',
                'featured' => false,
                'categories' => ['Programming', 'Technology'],
                'tags' => ['Database', 'Best Practice', 'Backend', 'Optimization'],
                'published_at' => now()->addDays(2),
                'views' => 0,
            ],
            [
                'title' => 'JavaScript ES6+ Features yang Wajib Dikuasai',
                'slug' => 'javascript-es6-features-yang-wajib-dikuasai',
                'excerpt' => 'Fitur-fitur modern JavaScript yang akan meningkatkan produktivitas Anda.',
                'content' => '<h2>Evolution JavaScript</h2><p>ES6 (ECMAScript 2015) membawa banyak fitur baru yang membuat JavaScript lebih powerful dan mudah dibaca. Mari kita pelajari fitur-fitur penting.</p><h3>1. Arrow Functions</h3><p>Syntax yang lebih ringkas untuk function:</p><pre><code>// Traditional function
function add(a, b) {
    return a + b;
}

// Arrow function
const add = (a, b) => a + b;

// Single parameter
const double = x => x * 2;</code></pre><h3>2. Destructuring</h3><p>Extract values dari objects dan arrays dengan mudah:</p><pre><code>// Object destructuring
const {name, age} = person;

// Array destructuring
const [first, second] = numbers;</code></pre><h3>3. Template Literals</h3><p>String interpolation yang lebih clean:</p><pre><code>const message = `Hello ${name}, you are ${age} years old.`;</code></pre>',
                'meta_title' => 'JavaScript ES6+ Features yang Wajib Dikuasai | Tutorial',
                'meta_description' => 'Pelajari fitur-fitur modern JavaScript ES6+ seperti arrow functions, destructuring, template literals, dan lainnya.',
                'status' => 'published',
                'categories' => ['Programming', 'Web Development'],
                'tags' => ['JavaScript', 'ES6', 'Frontend', 'Tutorial'],
                'published_at' => now(),
                'views' => 234,
            ],
            [
                'title' => 'Optimasi Performa Website: Tips dan Trik',
                'slug' => 'optimasi-performa-website-tips-dan-trik',
                'excerpt' => 'Teknik-teknik untuk membuat website Anda lebih cepat dan efisien.',
                'content' => '<h2>Kenapa Performa Penting?</h2><p>Website yang cepat tidak hanya meningkatkan user experience, tapi juga mempengaruhi SEO ranking. Google menggunakan page speed sebagai salah satu faktor ranking.</p><h3>1. Image Optimization</h3><p>Images sering menjadi penyebab utama lambatnya website:</p><ul><li><strong>Compress images:</strong> Gunakan tools seperti TinyPNG</li><li><strong>Use modern formats:</strong> WebP, AVIF</li><li><strong>Lazy loading:</strong> Load images saat needed</li><li><strong>Responsive images:</strong> Gunakan srcset attribute</li></ul><pre><code>&lt;img src="image.webp"
     srcset="image-small.webp 480w, image-medium.webp 768w, image-large.webp 1024w"
     sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw"
     loading="lazy"&gt;</code></pre><h3>2. Code Minification</h3><p>Minify CSS dan JavaScript untuk mengurangi file size:</p><pre><code>// CSS minification example
.header {background:#fff;padding:1rem}</code></pre>',
                'meta_title' => 'Optimasi Performa Website: Tips dan Trik | Performance Guide',
                'meta_description' => 'Teknik-teknik untuk membuat website Anda lebih cepat dan efisien. Pelajari image optimization, caching, minification, dan lainnya.',
                'status' => 'published',
                'featured' => false,
                'categories' => ['Technology', 'Web Development'],
                'tags' => ['Performance', 'Optimization', 'Frontend', 'Best Practice'],
                'published_at' => now()->subHours(6),
                'views' => 189,
            ],
            [
                'title' => 'Security Best Practices untuk Laravel Applications',
                'slug' => 'security-best-practices-untuk-laravel-applications',
                'excerpt' => 'Panduan lengkap mengamankan aplikasi Laravel dari berbagai ancaman.',
                'content' => '<h2>Kenapa Security Penting?</h2><p>Security adalah aspek krusial dalam web development. Aplikasi yang tidak aman bisa menjadi target berbagai serangan cyber.</p><h3>1. Input Validation</h3><p>Validasi semua input dari user:</p><pre><code>use Illuminate\\Validation\\Rule;

$request->validate([
    \'email\' => [\'required\', \'email\', \'max:255\'],
    \'password\' => [\'required\', \'string\', \'min:8\', \'confirmed\'],
]);</code></pre><h3>2. SQL Injection Prevention</h3><p>Laravel Eloquent ORM sudah melindungi dari SQL injection:</p><pre><code>// Aman (menggunakan parameter binding)
User::where(\'email\', $email)->first();

// Hindari raw query yang tidak aman
// DB::select("SELECT * FROM users WHERE email = \'$email\'"); // DANGEROUS!</code></pre><h3>3. CSRF Protection</h3><p>Laravel sudah menyediakan CSRF protection otomatis:</p><pre><code>&lt;form method="POST" action="/profile"&gt;
    @csrf
    &lt;!-- form fields --&gt;
&lt;/form&gt;</code></pre>',
                'meta_title' => 'Security Best Practices untuk Laravel Applications | Security Guide',
                'meta_description' => 'Panduan lengkap mengamankan aplikasi Laravel dari berbagai ancaman. Pelajari validation, CSRF, XSS, dan security best practices.',
                'status' => 'draft',
                'featured' => false,
                'categories' => ['Programming', 'Technology'],
                'tags' => ['Security', 'Laravel', 'Backend', 'Best Practice'],
                'published_at' => now()->addDays(1),
                'views' => 0,
            ],
            [
                'title' => 'Vue.js vs React: Mana yang Lebih Baik?',
                'slug' => 'vuejs-vs-react-mana-yang-lebih-baik',
                'excerpt' => 'Perbandingan lengkap antara Vue.js dan React untuk membantu Anda memilih.',
                'content' => '<h2>Introduction</h2><p>Vue.js dan React adalah dua framework JavaScript paling populer saat ini. Keduanya memiliki kelebihan dan kekurangan masing-masing.</p><h3>Vue.js Overview</h3><p><strong>Kelebihan Vue.js:</strong></p><ul><li>Learning curve yang lebih mudah</li><li>Documentation yang sangat baik</li><li>Performance yang excellent</li><li>Flexible dan progressive</li></ul><h3>React Overview</h3><p><strong>Kelebihan React:</strong></p><ul><li>Ecosystem yang besar</li><li>Community support yang kuat</li><strong>Job opportunities yang banyak</strong></li><li>Facebook backing</li></ul><h3>Perbandingan Features</h3><table><tr><th>Feature</th><th>Vue.js</th><th>React</th></tr><tr><td>Learning Curve</td><td>Mudah</td><td>Sedang</td></tr><tr><td>Performance</td><td>Excellent</td><td>Good</td></tr><tr><td>Ecosystem</td><td>Good</td><td>Excellent</td></tr><tr><td>Bundle Size</td><td>Kecil</td><td>Sedang</td></tr></table>',
                'meta_title' => 'Vue.js vs React: Mana yang Lebih Baik? | Comparison',
                'meta_description' => 'Perbandingan lengkap antara Vue.js dan React. Pelajari kelebihan, kekurangan, dan kapan menggunakan masing-masing framework.',
                'status' => 'published',
                'categories' => ['Programming', 'Technology'],
                'tags' => ['Vue.js', 'React', 'JavaScript', 'Frontend', 'Comparison'],
                'published_at' => now()->subHours(12),
                'views' => 567,
            ],
            [
                'title' => 'Git Workflow untuk Tim Development',
                'slug' => 'git-workflow-untuk-tim-development',
                'excerpt' => 'Best practices menggunakan Git untuk kolaborasi tim yang efisien.',
                'content' => '<h2>Kenapa Git Workflow Penting?</h2><p>Git workflow yang baik memastikan kolaborasi tim berjalan lancar dan kode tetap terorganisir.</p><h3>1. Branching Strategy</h3><p>Gunakan branching strategy yang konsisten:</p><pre><code>main          // Production branch
develop       // Development branch
feature/*     // Feature branches
hotfix/*      // Hotfix branches
release/*     // Release branches</code></pre><h3>2. Commit Message Convention</h3><p>Gunakan commit message yang jelas dan konsisten:</p><pre><code>feat: add user authentication
fix: resolve login bug
docs: update API documentation
style: format CSS code
refactor: optimize database query
test: add unit tests for user service</code></pre><h3>3. Code Review Process</h3><p>Implement code review untuk maintain code quality:</p><ul><li>Pull request untuk setiap feature</li><li>Minimal 1 reviewer sebelum merge</li><li>Automated tests harus pass</li><li>Code coverage minimum 80%</li></ul>',
                'meta_title' => 'Git Workflow untuk Tim Development | Best Practices',
                'meta_description' => 'Best practices menggunakan Git untuk kolaborasi tim yang efisien. Pelajari branching strategy, commit convention, dan code review process.',
                'status' => 'published',
                'featured' => false,
                'categories' => ['Technology', 'Programming'],
                'tags' => ['Git', 'Version Control', 'Team Work', 'Best Practice'],
                'published_at' => now()->subHours(18),
                'views' => 345,
            ]
        ];

        foreach ($posts as $postData) {
            // Create the post
            $post = BlogPost::create([
                'user_id' => 1, // Admin user
                'title' => $postData['title'],
                'slug' => $postData['slug'],
                'excerpt' => $postData['excerpt'],
                'content' => $postData['content'],
                'status' => $postData['status'],
                'published_at' => $postData['published_at'] ?? now(),
                'views' => $postData['views'] ?? 0,
            ]);

            // Attach categories
            foreach ($postData['categories'] as $categoryName) {
                $category = BlogCategory::where('name', $categoryName)->first();
                if ($category) {
                    $post->categories()->attach($category->id);
                }
            }

            // Attach tags
            foreach ($postData['tags'] as $tagName) {
                $tag = BlogTag::where('name', $tagName)->first();
                if ($tag) {
                    $post->tags()->attach($tag->id);
                }
            }
        }

        $this->command->info('Blog posts seeded successfully!');
    }
}
