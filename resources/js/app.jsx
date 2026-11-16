import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';

const renderBlock = (block, idx) => {
    if (!block) return null;
    switch (block.type) {
        case 'paragraph':
            return <p key={idx} className="leading-relaxed text-body whitespace-pre-line">{block.content}</p>;
        case 'heading1':
            return <h2 key={idx} className="text-4xl font-bold text-heading mt-12 mb-6">{block.content}</h2>;
        case 'heading2':
            return <h3 key={idx} className="text-3xl font-bold text-heading mt-10 mb-4">{block.content}</h3>;
        case 'heading3':
            return <h4 key={idx} className="text-2xl font-bold text-heading mt-8 mb-4">{block.content}</h4>;
        case 'heading4':
            return <h5 key={idx} className="text-xl font-bold text-heading mt-6 mb-3">{block.content}</h5>;
        case 'heading5':
            return <h6 key={idx} className="text-lg font-bold text-heading mt-5 mb-2">{block.content}</h6>;
        case 'heading6':
            return <h6 key={idx} className="text-base font-bold text-heading mt-4 mb-2 uppercase tracking-wide">{block.content}</h6>;
        case 'image':
            const src = block.content?.startsWith('http') || block.content?.startsWith('/') ? block.content : `/${block.content}`;
            let widthClass = 'w-full';
            if (block.size === 'small') widthClass = 'w-32 md:w-48'; // 8rem / 12rem
            else if (block.size === 'medium') widthClass = 'w-64 md:w-96'; // 16rem / 24rem
            else if (block.size === 'large') widthClass = 'w-full max-w-[800px]';
            return (
                <div key={idx} className="my-8 flex justify-center">
                    <img src={src} alt="" className={`${widthClass} h-auto rounded-xl shadow-sm object-contain`} />
                </div>
            );
        case 'quote':
            return (
                <blockquote key={idx} className="border-l-4 border-primary pl-6 py-2 my-8 italic text-2xl text-heading/80">
                    {block.content}
                </blockquote>
            );
        case 'code':
            return (
                <pre key={idx} className="bg-[#211B17] text-cream p-6 rounded-xl overflow-x-auto text-sm my-6 shadow-inner">
                    <code>{block.content}</code>
                </pre>
            );
        case 'list':
            const items = block.content ? block.content.split('\n').filter(i => i.trim() !== '') : [];
            return (
                <ul key={idx} className="list-disc list-inside space-y-3 my-6 pl-4 marker:text-primary">
                    {items.map((item, iIdx) => (
                        <li key={iIdx}>{item}</li>
                    ))}
                </ul>
            );
        case 'columns':
            let layoutClass = "flex flex-col md:flex-row gap-8 my-8 items-start";
            
            return (
                <div key={idx} className={layoutClass}>
                    {(block.columns || []).map((col, cIdx) => {
                        let colClass = "flex-1 w-full"; // equal by default
                        if (block.layout === 'auto') colClass = "flex-auto w-full md:w-auto";
                        else if (block.layout === 'left-heavy') colClass = cIdx === 0 ? "flex-[2] w-full" : "flex-[1] w-full";
                        else if (block.layout === 'right-heavy') colClass = cIdx === 1 ? "flex-[2] w-full" : "flex-[1] w-full";
                        
                        return (
                            <div key={cIdx} className={`${colClass} space-y-4`}>
                                {(col || []).map((childBlock, cbIdx) => renderBlock(childBlock, `${idx}-${cIdx}-${cbIdx}`))}
                            </div>
                        )
                    })}
                </div>
            );
        default:
            return null;
    }
};

const Home = () => {
    const [scrolled, setScrolled] = useState(false);
    const settings = window.HOMEPAGE_SETTINGS || {};

    useEffect(() => {
        const handleScroll = () => {
            setScrolled(window.scrollY > 50);
        };
        window.addEventListener('scroll', handleScroll);
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <div className="min-h-screen bg-cream text-body font-sans selection:bg-primary selection:text-white">
            {/* Navbar */}
            <nav className={`fixed w-full z-50 transition-all duration-300 ${scrolled ? 'bg-card/90 backdrop-blur-md shadow-sm py-4 border-b border-border' : 'bg-transparent py-6'}`}>
                <div className="max-w-7xl mx-auto px-6 flex justify-between items-center">
                    <div className="flex items-center gap-2">
                        {settings.header_logo_type === 'both' && settings.header_logo_image ? (
                            <>
                                <img src={`/${settings.header_logo_image}`} alt="Logo" className="h-10" />
                                <span className="text-xl font-bold text-heading">
                                    {settings.header_logo_text || 'EdOrbit'}
                                </span>
                            </>
                        ) : settings.header_logo_type === 'image' && settings.header_logo_image ? (
                            <img src={`/${settings.header_logo_image}`} alt="Logo" className="h-10" />
                        ) : (
                            <>
                                <div className="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-primary/30">
                                    {settings.header_logo_text ? settings.header_logo_text.charAt(0) : 'E'}
                                </div>
                                <span className="text-xl font-bold text-heading">
                                    {settings.header_logo_text || 'EdOrbit'}
                                </span>
                            </>
                        )}
                    </div>
                    
                    <div className="hidden md:flex items-center space-x-8 font-medium text-body">
                        {(settings.header_menus || []).map((menu, idx) => (
                            <div key={idx} className="relative group">
                                <a href={menu.url} className="hover:text-primary transition-colors flex items-center gap-1">
                                    {menu.label}
                                    {menu.children && menu.children.length > 0 && (
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                                        </svg>
                                    )}
                                </a>
                                {menu.children && menu.children.length > 0 && (
                                    <div className="absolute top-full left-0 mt-2 w-48 bg-card border border-border rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-left scale-95 group-hover:scale-100 flex flex-col py-2">
                                        {menu.children.map((child, cIdx) => (
                                            <a key={cIdx} href={child.url} className="px-4 py-2 hover:bg-cream hover:text-primary text-sm transition-colors">
                                                {child.label}
                                            </a>
                                        ))}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>

                    <div className="flex items-center gap-4">
                        {(settings.header_buttons || []).map((btn, idx) => {
                            const hasChildren = btn.children && btn.children.length > 0;
                            const chevron = hasChildren && (
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                                </svg>
                            );
                            let btnEl;
                            if (btn.style === 'text') {
                                btnEl = (
                                    <a href={btn.url} className="font-medium text-body hover:text-primary transition-colors flex items-center gap-1">
                                        {btn.label}{chevron}
                                    </a>
                                );
                            } else if (btn.style === 'outline') {
                                btnEl = (
                                    <a href={btn.url} className="border border-border hover:border-primary text-heading hover:text-primary px-6 py-2 rounded-full font-medium transition-all duration-300 shadow-sm hover:shadow-primary/10 flex items-center gap-1">
                                        {btn.label}{chevron}
                                    </a>
                                );
                            } else {
                                btnEl = (
                                    <a href={btn.url} className="bg-heading hover:bg-primary text-white px-6 py-2.5 rounded-full font-medium transition-all duration-300 shadow-md hover:shadow-primary/25 transform hover:-translate-y-0.5 inline-flex items-center gap-1">
                                        {btn.label}{chevron}
                                    </a>
                                );
                            }
                            return (
                                <div key={idx} className={`relative group ${btn.style === 'text' ? 'hidden md:block' : ''}`}>
                                    {btnEl}
                                    {hasChildren && (
                                        <div className="absolute top-full right-0 mt-2 w-48 bg-card border border-border rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-right scale-95 group-hover:scale-100 flex flex-col py-2">
                                            {btn.children.map((child, cIdx) => (
                                                <a key={cIdx} href={child.url} className="px-4 py-2 hover:bg-cream hover:text-primary text-sm transition-colors">
                                                    {child.label}
                                                </a>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>
            </nav>

            {(!window.PAGE_DATA || settings.homepage_type === 'both') && (
                // --- HOMEPAGE CONTENT ---
                <main>
                    {/* Hero Section */}
                    <header className="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
                <div className="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
                    <div className="absolute -top-[20%] -right-[10%] w-[70%] h-[70%] rounded-full bg-primary-light blur-3xl opacity-60"></div>
                    <div className="absolute top-[40%] -left-[10%] w-[50%] h-[50%] rounded-full bg-beige blur-3xl opacity-60"></div>
                </div>
                
                <div className="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
                    <div className="max-w-2xl">
                        <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-light border border-primary/20 text-primary-dark font-medium text-sm mb-6 animate-fade-in-up">
                            <span className="relative flex h-2.5 w-2.5">
                              <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                              <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span>
                            </span>
                            {settings.hero_subtitle}
                        </div>
                        {settings.hero_title && settings.hero_title.includes('<') ? (
                            // Legacy titles saved earlier as HTML
                            <h1 
                                className="text-5xl lg:text-7xl font-extrabold tracking-tight mb-8 leading-tight text-heading"
                                dangerouslySetInnerHTML={{ __html: settings.hero_title }}
                            />
                        ) : (
                            <h1 className="text-5xl lg:text-7xl font-extrabold tracking-tight mb-8 leading-tight text-heading">
                                {(() => {
                                    const title = settings.hero_title || '';
                                    const hl = (settings.hero_title_highlight || '').trim();
                                    const pos = hl ? title.indexOf(hl) : -1;
                                    if (pos === -1) return title;
                                    return (
                                        <>
                                            {title.slice(0, pos)}
                                            <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">{hl}</span>
                                            {title.slice(pos + hl.length)}
                                        </>
                                    );
                                })()}
                            </h1>
                        )}
                        <p className="text-lg text-body mb-10 leading-relaxed max-w-lg">
                            {settings.hero_description}
                        </p>
                        {(() => {
                            const heroButtons = (settings.hero_buttons || []).filter(b => b.enabled !== false && b.label);
                            if (heroButtons.length === 0) return null;
                            const base = "px-8 py-4 rounded-full font-medium text-lg transition-all inline-flex items-center gap-2";
                            const variants = {
                                primary: "bg-primary hover:bg-primary-dark text-white shadow-xl shadow-primary/30 transform hover:-translate-y-1",
                                dark: "bg-heading hover:bg-primary text-white shadow-xl transform hover:-translate-y-1",
                                soft: "bg-card hover:bg-beige text-heading border border-border shadow-sm hover:shadow transform hover:-translate-y-1",
                                outline: "bg-transparent border-2 border-primary text-primary hover:bg-primary hover:text-white transform hover:-translate-y-1",
                                text: "bg-transparent text-heading hover:text-primary px-4",
                            };
                            return (
                                <div className="flex flex-wrap items-center gap-4">
                                    {heroButtons.map((b, i) => (
                                        <a key={i} href={b.url || '#'} className={`${base} ${variants[b.style] || variants.primary}`}>
                                            {b.icon && (
                                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clipRule="evenodd" />
                                                </svg>
                                            )}
                                            {b.label}
                                        </a>
                                    ))}
                                </div>
                            );
                        })()}
                        
                        {(() => {
                            const stats = settings.stats || {};
                            const items = (stats.items || []).filter(s => s.enabled !== false && (s.value || s.label));
                            if (stats.enabled === false || items.length === 0) return null;
                            return (
                                <div
                                    className="mt-16 grid gap-6 pt-8 border-t border-border"
                                    style={{ gridTemplateColumns: `repeat(${Math.min(items.length, 3)}, minmax(0, 1fr))` }}
                                >
                                    {items.map((s, i) => (
                                        <div key={i}>
                                            <h3 className="text-3xl font-bold text-heading mb-1">{s.value}</h3>
                                            <p className="text-sm text-body font-medium">{s.label}</p>
                                        </div>
                                    ))}
                                </div>
                            );
                        })()}
                    </div>
                    
                    <div className="relative lg:h-[600px] flex items-center justify-center">
                        <div className="absolute inset-0 bg-primary-light rounded-[3rem] rotate-3 scale-105 transition-transform duration-700 hover:rotate-6"></div>
                        <img 
                            src={settings.hero_image ? `/${settings.hero_image}` : "https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2070&auto=format&fit=crop"} 
                            alt="Students on campus" 
                            className="relative z-10 rounded-[2.5rem] shadow-2xl object-cover h-full w-full border-4 border-card"
                        />
                        
                        {/* Floating Badge */}
                        <div className="absolute -bottom-6 -left-6 z-20 bg-card p-5 rounded-2xl shadow-xl border border-border animate-bounce" style={{animationDuration: '3s'}}>
                            <div className="flex items-center gap-4">
                                <div className="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p className="text-sm text-body font-medium">Ranked #1</p>
                                    <p className="font-bold text-heading">in Innovation</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {/* Features Section */}
            {(() => {
                const features = settings.features || {};
                const items = (features.items || []).filter(f => f.enabled !== false && (f.title || f.desc));
                if (features.enabled === false || items.length === 0) return null;
                const iconPaths = {
                    book: "M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253",
                    flask: "M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z",
                    smile: "M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
                    star: "M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z",
                    users: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
                    globe: "M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
                    heart: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z",
                    cap: "M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222",
                    bulb: "M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z",
                };
                return (
                    <section className="py-24 bg-card relative">
                        <div className="max-w-7xl mx-auto px-6">
                            {(features.tag || features.title) && (
                                <div className="text-center max-w-2xl mx-auto mb-16">
                                    {features.tag && <h2 className="text-primary font-bold tracking-wide uppercase text-sm mb-3">{features.tag}</h2>}
                                    {features.title && <h3 className="text-3xl md:text-4xl font-bold text-heading mb-4">{features.title}</h3>}
                                </div>
                            )}

                            <div className="grid md:grid-cols-3 gap-8">
                                {items.map((f, i) => (
                                    <div key={i} className={`group ${f.image ? 'p-0 overflow-hidden bg-white' : 'p-8 bg-cream'} rounded-3xl border border-border hover:border-primary-light hover:shadow-xl hover:shadow-primary/10 transition-all duration-300 flex flex-col`}>
                                        {f.image ? (
                                            <div className="w-full h-48 overflow-hidden bg-white flex items-center justify-center p-4 border-b border-border/50">
                                                <img src={`/${f.image}`} alt={f.title} className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500" />
                                            </div>
                                        ) : (
                                            <div className="w-14 h-14 rounded-2xl bg-primary-light text-primary flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all duration-300 mx-auto md:mx-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" className="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={iconPaths[f.icon] || iconPaths.star} />
                                                </svg>
                                            </div>
                                        )}
                                        <div className={f.image ? 'p-8 flex-1 flex flex-col bg-white' : 'flex-1 flex flex-col'}>
                                            <h4 className="text-xl font-bold text-heading mb-3">{f.title}</h4>
                                            <p className="text-body leading-relaxed">{f.desc}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </section>
                );
            })()}
            
            {/* Testimonials Section */}
            {(() => {
                const testimonials = settings.testimonials || {};
                const items = (testimonials.items || []).filter(t => t.enabled !== false && (t.text || t.author));
                if (testimonials.enabled === false || items.length === 0) return null;
                
                return (
                    <section className="py-24 bg-cream relative">
                        <div className="max-w-7xl mx-auto px-6">
                            {(testimonials.tag || testimonials.title) && (
                                <div className="text-center max-w-2xl mx-auto mb-16">
                                    {testimonials.tag && <h2 className="text-primary font-bold tracking-wide uppercase text-sm mb-3">{testimonials.tag}</h2>}
                                    {testimonials.title && <h3 className="text-3xl md:text-4xl font-bold text-heading mb-4">{testimonials.title}</h3>}
                                </div>
                            )}

                            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                                {items.map((t, i) => (
                                    <div key={i} className="bg-white p-8 rounded-3xl border border-border shadow-sm hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 flex flex-col">
                                        <div className="text-primary mb-6">
                                            <svg className="w-10 h-10 opacity-40" fill="currentColor" viewBox="0 0 32 32" aria-hidden="true">
                                                <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z" />
                                            </svg>
                                        </div>
                                        <p className="text-heading text-lg italic leading-relaxed flex-1 mb-8">"{t.text}"</p>
                                        <div className="flex items-center gap-4 mt-auto">
                                            {t.image ? (
                                                <img src={`/${t.image}`} alt={t.author} className="w-12 h-12 rounded-full object-cover border-2 border-primary/20" />
                                            ) : (
                                                <div className="w-12 h-12 rounded-full bg-primary-light flex items-center justify-center text-primary font-bold text-lg">
                                                    {t.author ? t.author.charAt(0).toUpperCase() : 'U'}
                                                </div>
                                            )}
                                            <div>
                                                <h4 className="font-bold text-heading">{t.author}</h4>
                                                <p className="text-sm text-body">{t.role}</p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </section>
                );
            })()}

            {/* CTA Section */}
            {(() => {
                const cta = settings.cta || {};
                if (cta.enabled === false) return null;
                const buttons = (cta.buttons || []).filter(b => b.enabled !== false && b.label);
                
                const variants = {
                    primary: "bg-primary hover:bg-primary-dark text-white shadow-lg hover:shadow-primary/25",
                    dark: "bg-heading hover:bg-black text-white shadow-lg",
                    soft: "bg-white/10 hover:bg-white/20 text-white backdrop-blur-md",
                    outline: "bg-transparent border-2 border-white/30 text-white hover:bg-white hover:text-primary",
                    text: "bg-transparent text-white hover:text-white/80",
                };

                return (
                    <section className="py-24 bg-heading relative overflow-hidden">
                        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-primary rounded-full blur-[120px] opacity-20"></div>
                        <div className="max-w-4xl mx-auto px-6 text-center relative z-10">
                            <h2 className="text-4xl md:text-5xl font-bold text-white mb-6">{cta.title}</h2>
                            <p className="text-xl text-cream mb-10 max-w-2xl mx-auto opacity-80">
                                {cta.subtitle}
                            </p>
                            {buttons.length > 0 && (
                                <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                                    {buttons.map((b, i) => (
                                        <a 
                                            key={i} 
                                            href={b.url || '#'} 
                                            className={`w-full sm:w-auto px-8 py-4 rounded-full font-medium text-lg transition-all ${variants[b.style] || variants.primary}`}
                                        >
                                            {b.label}
                                        </a>
                                    ))}
                                </div>
                            )}
                        </div>
                    </section>
                );
            })()}
            
                </main>
            )}
            
            {window.PAGE_DATA && (
                // --- DYNAMIC PAGE CONTENT ---
                <main className={`${settings.homepage_type === 'both' ? 'py-16' : 'pt-32 pb-24'} min-h-[30vh] bg-cream`}>
                    <div className="max-w-4xl mx-auto px-6">
                        <div className="space-y-6 text-lg">
                            {(window.PAGE_DATA.blocks || []).map((block, idx) => renderBlock(block, idx))}
                        </div>
                    </div>
                </main>
            )}

            {/* Footer */}
            <footer className="bg-[#171411] text-body py-12 border-t border-[#2e2520]">
                <div className={`max-w-7xl mx-auto px-6 grid md:grid-cols-${settings.footer_columns?.filter(c => c.enabled !== false).length || 1} gap-8 mb-8`}>
                    {(settings.footer_columns || []).filter(c => c.enabled !== false).map((col, idx) => (
                        <div key={idx}>
                            {col.title && <h4 className="text-white font-semibold mb-4">{col.title}</h4>}
                            
                            {col.type === 'about' && (
                                <div>
                                    {(col.image || !col.title) && (
                                        <div className="flex items-center gap-3 mb-4">
                                            {col.image ? (
                                                <img src={`/${col.image}`} alt="Logo" className="h-12 object-contain" />
                                            ) : (
                                                <>
                                                    <div className="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white font-bold">
                                                        {settings.header_logo_text ? settings.header_logo_text.charAt(0) : 'E'}
                                                    </div>
                                                    <span className="text-lg font-bold text-white">
                                                        {settings.header_logo_text || 'EdOrbit'}
                                                    </span>
                                                </>
                                            )}
                                        </div>
                                    )}
                                    <p className="text-sm text-white/70 leading-relaxed">{col.text}</p>
                                </div>
                            )}

                            {col.type === 'links' && (
                                <ul className="space-y-2 text-sm text-white/70">
                                    {(col.links || []).filter(l => l.enabled !== false).map((link, li) => (
                                        <li key={li}><a href={link.url || '#'} className="hover:text-primary transition-colors">{link.label}</a></li>
                                    ))}
                                </ul>
                            )}

                            {col.type === 'contact' && (
                                <ul className="space-y-2 text-sm text-white/70">
                                    {col.email && <li><a href={`mailto:${col.email}`} className="hover:text-primary transition-colors">{col.email}</a></li>}
                                    {col.phone && <li><a href={`tel:${col.phone}`} className="hover:text-primary transition-colors">{col.phone}</a></li>}
                                    {col.address && <li className="whitespace-pre-line">{col.address}</li>}
                                </ul>
                            )}
                        </div>
                    ))}
                </div>
                <div className="max-w-7xl mx-auto px-6 text-center text-sm border-t border-[#2e2520] pt-8 text-body">
                    &copy; {new Date().getFullYear()} {settings.header_logo_text || 'EdOrbit School'}. All rights reserved.
                </div>
            </footer>
        </div>
    );
};

const root = createRoot(document.getElementById('app'));
root.render(<Home />);
