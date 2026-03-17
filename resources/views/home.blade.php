@extends('layouts.app')

@section('title', 'Student-Freelancer Hub | Kelola Kuliah & Karirmu')

@section('content')
    @include('home.hero')

    @include('home.registration')

@endsection

@push('scripts')
    <script>
        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const email = this.querySelector('input[type="email"]');
                if (email && !email.value.includes("@")) {
                    e.preventDefault();
                    alert("Silakan masukkan email yang valid.");
                }
            });
        });

        // Plan selection styling
        document.querySelectorAll('input[name="plan"]').forEach((radio) => {
            radio.addEventListener("change", function() {
                document.querySelectorAll("label").forEach((label) => {
                    label.classList.remove("border-orange-300", "bg-orange-50",
                        "dark:bg-orange-900/40", "dark:border-orange-500");
                    label.classList.add("dark:hover:border-orange-500");
                });

                if (this.checked) {
                    const label = this.closest("label");
                    label.classList.remove("dark:hover:border-orange-500");
                    label.classList.add("border-orange-300", "bg-orange-50", "dark:bg-orange-900/40",
                        "dark:border-orange-500");
                }
            });
        });
    </script>
@endpush
