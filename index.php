<?php
/**
 * Index Page - Landing Page
 * Welcome page for the Sports Club Management System
 */

// Page configuration
$pageTitle = 'page.dashboard_title';
$pageHeading = 'page.dashboard_title';
$activePage = 'dashboard';

// Custom styles for index/landing page
$customStyles = '
        body {
            background: linear-gradient(to bottom, #f8fafc 0%, #e2e8f0 100%);
        }
        .hero-section {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .feature-card {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .action-button {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: white;
            color: #1e3a8a;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
';

// No additional links needed
$additionalLinks = [];

// Set base path for root level
$basePath = '';

// Include header
include 'includes/header.php';
?>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4" data-i18n="page.welcome_title">
                ක්‍රීඩා සමාජ කළමනාකරණ පද්ධතිය
            </h1>
            <p class="text-xl mb-6 text-blue-100" data-i18n="page.welcome_subtitle">
                දකුණු පළාත් ක්‍රීඩා අමාත්‍යාංශය
            </p>
            <p class="text-lg mb-8 text-blue-50" data-i18n="page.welcome_description">
                ක්‍රීඩා සමාජ ලියාපදිංචි කිරීම සහ කළමනාකරණය සඳහා වන ඩිජිටල් පද්ධතිය
            </p>
            <div class="flex justify-center gap-4 flex-wrap">
                <a href="public/register.php" class="action-button" data-i18n="button.register_new_club">
                    නව සමාජයක් ලියාපදිංචි කරන්න
                </a>
                <a href="public/dashboard.php" class="action-button" data-i18n="button.view_clubs">
                    සමාජ බලන්න
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        
        <!-- Features Section -->
        <div class="mb-12">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-8" data-i18n="page.features_title">
                පද්ධතියේ ලක්ෂණ
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1: Registration -->
                <div class="feature-card">
                    <div class="text-center">
                        <div class="feature-icon">📝</div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3" data-i18n="feature.registration_title">
                            ඉක්මන් ලියාපදිංචිය
                        </h3>
                        <p class="text-gray-600" data-i18n="feature.registration_description">
                            සරල සහ ඉක්මන් ඔන්ලයින් ලියාපදිංචි ක්‍රියාවලිය. දිස්ත්‍රික්කය, කොට්ඨාසය සහ ග්‍රාම නිලධාරී වසම මත පදනම්ව ස්වයංක්‍රීය ලියාපදිංචි අංක උත්පාදනය.
                        </p>
                    </div>
                </div>

                <!-- Feature 2: Management -->
                <div class="feature-card">
                    <div class="text-center">
                        <div class="feature-icon">📊</div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3" data-i18n="feature.management_title">
                            කළමනාකරණය
                        </h3>
                        <p class="text-gray-600" data-i18n="feature.management_description">
                            සියලුම ලියාපදිංචි සමාජ බලන්න, සොයන්න සහ කළමනාකරණය කරන්න. දිස්ත්‍රික්කය අනුව පෙරීම සහ සත්‍ය කාලීන සංඛ්‍යා ලේඛන.
                        </p>
                    </div>
                </div>

                <!-- Feature 3: Multi-language -->
                <div class="feature-card">
                    <div class="text-center">
                        <div class="feature-icon">🌐</div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3" data-i18n="feature.multilingual_title">
                            බහු භාෂා සහාය
                        </h3>
                        <p class="text-gray-600" data-i18n="feature.multilingual_description">
                            සිංහල, ඉංග්‍රීසි සහ දෙමළ භාෂා සහිතව පූර්ණ බහු භාෂා සහාය. භාෂාව ඕනෑම වේලාවක මාරු කරන්න.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Section -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6" data-i18n="page.quick_stats_title">
                ඉක්මන් සංඛ්‍යා ලේඛන
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-4xl font-bold text-blue-600 mb-2">3</div>
                    <div class="text-gray-700" data-i18n="stats.districts">දිස්ත්‍රික්ක</div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-4xl font-bold text-green-600 mb-2">∞</div>
                    <div class="text-gray-700" data-i18n="stats.clubs">ක්‍රීඩා සමාජ</div>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-4xl font-bold text-purple-600 mb-2">24/7</div>
                    <div class="text-gray-700" data-i18n="stats.availability">ලබා ගත හැකිය</div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold mb-4" data-i18n="page.cta_title">
                අදම ආරම්භ කරන්න
            </h2>
            <p class="text-lg mb-6 text-blue-100" data-i18n="page.cta_description">
                ඔබේ ක්‍රීඩා සමාජය ලියාපදිංචි කර ඩිජිටල් කළමනාකරණ පද්ධතියේ ප්‍රතිලාභ ලබා ගන්න
            </p>
            <a href="public/register.php" class="action-button" data-i18n="button.register_now">
                දැන් ලියාපදිංචි කරන්න
            </a>
        </div>

    </main>

<?php
// No additional scripts needed for index page
$scripts = [];

// Base path is already set above
// Include footer
include 'includes/footer.php';
?>
