<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vidya Niketan Academy | Home (React Edition)</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#094cb2',
                        'primary-light': '#1a65d6',
                        'surface-dim': '#f4f3fa',
                    },
                    fontFamily: {
                        serif: ['"Noto Serif"', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                        label: ['"Public Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- React & ReactDOM -->
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <!-- Babel for JSX -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Noto+Serif:ital,wght@0,400;0,600;0,700;1,400&family=Public+Sans:wght@500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fdfbff; color: #1a1b1f; }
        .hero-section { background: linear-gradient(180deg, #fdfbff 0%, #f4f3fa 100%); }
    </style>
</head>
<body>
    <?php
        $settings = \App\Models\WebsiteSetting::getSettings();
    ?>
    <script>
        window.websiteSettings = <?php echo json_encode($settings, 15, 512) ?>;
        
        // Also update Tailwind colors dynamically if they exist
        if (window.websiteSettings.color_primary) {
            tailwind.config.theme.extend.colors.primary = window.websiteSettings.color_primary;
        }
    </script>
    <div id="root"></div>

    <script type="text/babel">
        function TopBar() {
            return (
                <div className="hidden md:flex bg-[#001b3e] text-[#d6e3ff] text-xs py-2 px-6 justify-between items-center font-label tracking-wide">
                    <div className="flex items-center gap-6">
                        <span>{window.websiteSettings.header_top_text || 'Affiliated to CBSE (Affiliation No: 123456)'}</span>
                    </div>
                    <div className="flex items-center gap-6">
                        <a href="#" className="hover:text-white transition">{window.websiteSettings.header_phone || '+91 (0) 98765 43210'}</a>
                        <a href="#" className="hover:text-white transition">Book a Campus Tour</a>
                    </div>
                </div>
            );
        }

        function Navbar() {
            return (
                <nav className="bg-white/95 backdrop-blur-md border-b border-black/5 sticky top-0 z-50 py-4 px-6">
                    <div className="max-w-7xl mx-auto flex justify-between items-center">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xl">
                                {window.websiteSettings.site_title ? window.websiteSettings.site_title.charAt(0) : 'V'}
                            </div>
                            <div>
                                <h2 className="text-xl font-bold text-primary font-serif leading-none">{window.websiteSettings.site_title || 'Vidya Niketan'}</h2>
                                <span className="text-xs font-semibold uppercase tracking-wider text-gray-500 font-sans">Academy</span>
                            </div>
                        </div>
                        <div className="hidden lg:flex items-center gap-6">
                            {['Home', 'About Us', 'Academics', 'Admissions', 'Facilities', 'Contact'].map(item => (
                                <a key={item} href="#" className="text-sm font-medium text-gray-900 hover:text-primary transition">{item}</a>
                            ))}
                        </div>
                        <div className="flex items-center gap-4">
                            <a href="#" className="hidden md:inline-flex bg-gradient-to-br from-primary to-primary-light text-white px-5 py-2.5 rounded-lg font-semibold text-sm font-label hover:-translate-y-px hover:shadow-lg transition">Apply for 2024-25</a>
                            <a href="/login" className="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition">
                                <span className="font-bold text-gray-600">L</span>
                            </a>
                        </div>
                    </div>
                </nav>
            );
        }

        function Hero() {
            return (
                <section className="hero-section py-20 px-6 overflow-hidden">
                    <div className="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                        <div>
                            <span className="text-sm font-semibold uppercase tracking-widest mb-4 block text-[#8e7343] font-label">Welcome to {window.websiteSettings.site_title || 'Vidya Niketan'}</span>
                            <h1 className="text-5xl lg:text-7xl font-serif text-[#001b3e] leading-[1.1] mb-6">{window.websiteSettings.hero_title || 'Nurturing Curious Minds, Inspiring Character.'}</h1>
                            <p className="text-lg text-gray-600 mb-8 max-w-lg">{window.websiteSettings.hero_subtitle || 'Empowering children from kindergarten through grade twelve to lead with intellectual integrity and empathy.'}</p>
                            <div className="flex gap-4 mb-12">
                                <button className="bg-gradient-to-br from-primary to-primary-light text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition">{window.websiteSettings.hero_btn1_text || 'Enroll Today'} &rarr;</button>
                                <button className="bg-surface-dim text-primary px-6 py-3 rounded-xl font-semibold hover:bg-gray-200 transition">{window.websiteSettings.hero_btn2_text || 'Virtual Tour'}</button>
                            </div>
                            <div className="flex gap-4">
                                <span className="bg-white px-4 py-1.5 rounded-full text-xs font-bold text-gray-500 uppercase tracking-wide shadow-sm border border-gray-100">25+ Yrs Heritage</span>
                                <span className="bg-white px-4 py-1.5 rounded-full text-xs font-bold text-gray-500 uppercase tracking-wide shadow-sm border border-gray-100">100% Board Excellence</span>
                            </div>
                        </div>
                        <div className="relative">
                            <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=1000" className="rounded-3xl shadow-2xl h-[500px] w-full object-cover" alt="Students" />
                            <div className="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl max-w-xs z-10 border border-gray-100">
                                <h4 className="font-bold text-sm mb-1">CLASSROOM INSIGHTS</h4>
                                <p className="text-xs text-gray-500 mb-2">Collaborative robotics sessions currently open for Secondary level.</p>
                                <a href="#" className="text-xs font-bold text-primary">Learn more &rarr;</a>
                            </div>
                        </div>
                    </div>
                </section>
            );
        }
        
        function Stats() {
            const stats = [
                { num: "25+", title: "Years Legacy" },
                { num: "100%", title: "Excellence" },
                { num: "45+", title: "Learning Spaces" },
                { num: "16:1", title: "Student Ratio" },
            ];
            return (
                <section className="py-12 bg-white relative z-10 -mt-10 mx-6">
                    <div className="max-w-6xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6">
                        {stats.map(s => (
                            <div key={s.title} className="bg-white p-8 rounded-2xl text-center shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-50 hover:-translate-y-1 transition">
                                <div className="text-4xl font-serif font-bold text-primary mb-2">{s.num}</div>
                                <div className="text-xs font-bold text-gray-400 uppercase tracking-wider font-label">{s.title}</div>
                            </div>
                        ))}
                    </div>
                </section>
            );
        }

        function App() {
            return (
                <div>
                    <TopBar />
                    <Navbar />
                    <Hero />
                    <Stats />
                    <section className="py-24 bg-surface-dim text-center">
                        <h2 className="text-3xl font-serif mb-4">Admissions Now Open for {window.websiteSettings.footer_year || '2024-25'}</h2>
                        <a href="/login" className="text-primary font-semibold hover:underline">Go to Admin Panel (/login) to configure website settings</a>
                    </section>
                    
                    <footer className="bg-[#001b3e] text-white py-12 px-6">
                        <div className="max-w-7xl mx-auto text-center">
                            <p className="mb-4">{window.websiteSettings.footer_desc || 'Nurturing intellectual curiosity, moral character, and social courage.'}</p>
                            <p className="text-sm text-gray-400">&copy; {window.websiteSettings.footer_year || '2025'} {window.websiteSettings.site_title || 'Vidya Niketan Academy'}. All Rights Reserved.</p>
                        </div>
                    </footer>
                </div>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>
<?php /**PATH C:\Users\visha\Downloads\edu_verse-10-05-26\edu_verse\resources\views/frontend/index.blade.php ENDPATH**/ ?>