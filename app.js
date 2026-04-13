document.addEventListener('DOMContentLoaded', () => {

    // Reveal elements on scroll
    const initRevealObserver = () => {
        const items = document.querySelectorAll(".reveal");
        if (!items.length) return;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("in-view");
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: "0px 0px -50px 0px" });
        
        items.forEach((item) => observer.observe(item));
    };

    // Masterclass Tabs Logic
    const initMasterclassTabs = () => {
        const tabs = document.querySelectorAll('.masterclass-tab');
        const panes = document.querySelectorAll('.masterclass-pane');
        const oilDrop = document.getElementById('oil-drop');
        if (!tabs.length) return;

        // Init drop position
        if (oilDrop && tabs[0]) {
            setTimeout(() => {
                const tabRect = tabs[0].getBoundingClientRect();
                const navRect = tabs[0].closest('.masterclass-nav').getBoundingClientRect();
                oilDrop.style.top = ((tabRect.top - navRect.top) + (tabRect.height / 2) - 8) + "px";
            }, 100);
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Move oil drop
                if (oilDrop) {
                    const tabRect = this.getBoundingClientRect();
                    const navRect = this.closest('.masterclass-nav').getBoundingClientRect();
                    const offset = (tabRect.top - navRect.top) + (tabRect.height / 2) - 8;
                    oilDrop.style.top = offset + "px";
                }

                // Deactivate all
                tabs.forEach(t => t.classList.remove('active'));
                panes.forEach(p => p.classList.remove('active'));

                // Activate clicked
                this.classList.add('active');
                const targetId = this.getAttribute('data-target');
                const targetPane = document.getElementById(targetId);
                if (targetPane) {
                    // Force reflow to restart animation
                    targetPane.classList.remove('active');
                    void targetPane.offsetWidth;
                    targetPane.classList.add('active');
                }
            });
        });
    };

    // Update footer year dynamically
    const updateYear = () => {
        const yearNodes = document.querySelectorAll('.footer-bottom p');
        yearNodes.forEach(node => {
            node.innerHTML = node.innerHTML.replace(/2026/, new Date().getFullYear());
        });
    };

    // 3D Card Hover Effect (Tilt)
    const initTiltEffect = () => {
        const cards = document.querySelectorAll('.philosophy-card');
        if (!cards.length) return;

        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                // Calculate from center
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                // Rotation intensity (max 10 degrees)
                const rotateX = ((y - centerY) / centerY) * -10;
                const rotateY = ((x - centerX) / centerX) * 10;

                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
                card.style.transition = 'box-shadow 0.4s ease, border-color 0.4s ease, transform 0.5s ease-out';
            });
            
            card.addEventListener('mouseenter', () => {
                card.style.transition = 'box-shadow 0.4s ease, border-color 0.4s ease, transform 0.05s linear';
            });
        });
    };

    // Scroll-Driven Philosophy Sequence
    const initPhilosophyScroll = () => {
        const track = document.getElementById('philosophy-track');
        const triggers = document.querySelectorAll('.scroll-trigger');
        const cards = document.querySelectorAll('.philosophy-card');
        const images = document.querySelectorAll('.xfade-img');
        
        const textWrapper = document.getElementById('phil-text-wrapper');
        const titleTarget = document.getElementById('phil-title');
        const tipTarget = document.getElementById('phil-tip');
        const progressBar = document.getElementById('phil-progress');

        if (!track || !triggers.length || !textWrapper) return;

        let activeIndex = 0;

        // 1. Intersection Observer for Triggers
        const observerOptions = {
            root: null,
            rootMargin: '-50% 0px -50% 0px', // Trigger line across middle of viewport
            threshold: 0
        };

        const updatePhilosophyUI = (newIndex) => {
            if (newIndex === activeIndex) return;

            const oldCard = cards[activeIndex];
            const newCard = cards[newIndex];
            
            // Left cards
            if (oldCard) oldCard.classList.remove('active');
            if (newCard) newCard.classList.add('active');

            // Images crossfade
            images.forEach((img, i) => {
                if (i === newIndex) img.classList.add('active');
                else img.classList.remove('active');
            });

            // Text sequence (fade out -> swap -> fade in slide up)
            textWrapper.classList.remove('content-fade-in');
            textWrapper.classList.add('content-fade-out');

            setTimeout(() => {
                const newTitle = newCard.getAttribute('data-title');
                const newTip = newCard.getAttribute('data-tip');

                if (titleTarget) titleTarget.textContent = newTitle;
                if (tipTarget) tipTarget.innerHTML = `<strong>The Tip:</strong> ${newTip}`;

                textWrapper.classList.remove('content-fade-out');
                void textWrapper.offsetWidth; // Force reflow
                textWrapper.classList.add('content-fade-in');
            }, 200);

            activeIndex = newIndex;
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const index = parseInt(entry.target.getAttribute('data-index'));
                    updatePhilosophyUI(index);
                }
            });
        }, observerOptions);

        triggers.forEach(trigger => observer.observe(trigger));

        // 2. Scroll Progress Bar logic
        window.addEventListener('scroll', () => {
            const rect = track.getBoundingClientRect();
            // maxScroll is total distance the viewport can traverse over the track height
            const maxScroll = rect.height - window.innerHeight;
            let progress = Math.abs(rect.top) / maxScroll;
            
            // Clamp progress between 0 and 1
            if (rect.top > 0) progress = 0;
            if (progress > 1) progress = 1;

            if (progressBar) {
                progressBar.style.transform = `scaleY(${progress})`;
            }
        });
    };

    // Fire Embers Canvas Effect
    const initEmbersCanvas = () => {
        const canvas = document.getElementById('embers-canvas');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];
        
        const resize = () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = canvas.parentElement.offsetHeight;
        };
        
        window.addEventListener('resize', resize);
        resize();

        class Particle {
            constructor() {
                this.reset();
                this.y = Math.random() * height; // initial random spread
            }
            
            reset() {
                this.x = Math.random() * width;
                this.y = height + Math.random() * 50;
                this.size = Math.random() * 2.5 + 1;
                this.speedX = Math.random() * 1.5 - 0.75;
                this.speedY = Math.random() * -1.5 - 0.5;
                this.life = Math.random() * 0.6 + 0.2; // opacity
                this.color = Math.random() > 0.5 ? '#ff8a80' : '#ffcc80';
            }
            
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                this.life -= 0.002;
                
                if (this.life <= 0 || this.y < -10) {
                    this.reset();
                }
            }
            
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = this.color;
                ctx.globalAlpha = Math.max(0, this.life);
                ctx.fill();
            }
        }
        
        for (let i = 0; i < 70; i++) {
            particles.push(new Particle());
        }
        
        const animate = () => {
            ctx.clearRect(0, 0, width, height);
            ctx.globalCompositeOperation = 'screen';
            
            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();
            }
            requestAnimationFrame(animate);
        };
        
        animate();
    };

    // Spice Dust Cursor Trail
    const initSpiceDust = () => {
        const section = document.querySelector('.grandma-section');
        if (!section) return;

        let isThrottled = false;
        section.addEventListener('mousemove', (e) => {
            if (isThrottled) return;
            isThrottled = true;
            setTimeout(() => isThrottled = false, 40); // Generate particle every 40ms

            const dust = document.createElement('div');
            dust.className = 'spice-dust';
            dust.style.left = (e.clientX + (Math.random() * 20 - 10)) + 'px';
            dust.style.top = (e.clientY + (Math.random() * 20 - 10)) + 'px';
            
            // Randomize color slightly (harissa red vs cumin orange/brown)
            const colors = ['#b71c1c', '#d32f2f', '#f57f17', '#8d6e63', '#4e342e'];
            dust.style.background = colors[Math.floor(Math.random() * colors.length)];
            
            document.body.appendChild(dust);

            // Trigger animation next frame
            requestAnimationFrame(() => {
                dust.style.transform = `translateY(${Math.random() * 40 + 20}px) rotate(${Math.random() * 90}deg)`;
                dust.style.opacity = '0';
            });

            // Cleanup DOM
            setTimeout(() => {
                dust.remove();
            }, 1000);
        });
    };

    // 3D Circular Carousel Logic
    const initWisdomCarousel = () => {
        const viewport = document.getElementById('wisdom-carousel');
        const ring = document.getElementById('carousel-ring');
        const cards = document.querySelectorAll('.carousel-card');
        const currentEl = document.getElementById('carousel-current');
        const totalEl = document.getElementById('carousel-total');

        if (!ring || !cards.length) return;

        const numCards = cards.length;
        const angleStep = 360 / numCards;
        // Dynamically calculate the perfect radius to prevent cards from clipping based on total quantity
        const radius = Math.round((400 / 2) / Math.tan(Math.PI / Math.max(numCards, 3)));
        let currentAngle = 0;
        let activeIndex = 0;
        let isAnimating = false;

        if (totalEl) totalEl.textContent = numCards;

        // Position each card around the ring on the Y axis
        const layoutRing = () => {
            cards.forEach((card, i) => {
                const angle = i * angleStep;
                card.style.transform = `rotateY(${angle}deg) translateZ(${radius}px)`;
            });
            // Push the pivot point backwards so the front card rests at Z=0
            ring.style.transform = `translateZ(-${radius}px) rotateY(${currentAngle}deg)`;
            updateActive();
        };

        const updateActive = () => {
            // Determine which index is currently facing front
            let normalizedAngle = (((-currentAngle) % 360) + 360) % 360;
            activeIndex = Math.round(normalizedAngle / angleStep) % numCards;

            cards.forEach((card, i) => {
                card.classList.toggle('active', i === activeIndex);
            });
            if (currentEl) currentEl.textContent = activeIndex + 1;
        };

        // Handle scroll/wheel events inside the carousel viewport
        viewport.addEventListener('wheel', (e) => {
            e.preventDefault();
            if (isAnimating) return;

            // Support both horizontal (trackpad) and vertical scrolling
            const isHorizontal = Math.abs(e.deltaX) > Math.abs(e.deltaY);
            const delta = isHorizontal ? e.deltaX : e.deltaY;
            
            // Ignore tiny micro-scrolls to prevent accidental triggers
            if (Math.abs(delta) < 15) return;

            isAnimating = true;

            const direction = delta > 0 ? -1 : 1;
            currentAngle += direction * angleStep;
            ring.style.transform = `translateZ(-${radius}px) rotateY(${currentAngle}deg)`;
            updateActive();

            setTimeout(() => {
                isAnimating = false;
            }, 500); // Shorter lock period to feel more responsive
        }, { passive: false });

        // Touch support for mobile swipe
        let touchStartX = 0;
        let touchStartY = 0;
        viewport.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }, { passive: true });

        viewport.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            
            const diffX = touchStartX - touchEndX;
            const diffY = touchStartY - touchEndY;
            
            const isHorizontal = Math.abs(diffX) > Math.abs(diffY);
            const diff = isHorizontal ? diffX : diffY;

            if (Math.abs(diff) < 30) return;
            if (isAnimating) return;
            isAnimating = true;

            const direction = diff > 0 ? -1 : 1;
            currentAngle += direction * angleStep;
            ring.style.transform = `translateZ(-${radius}px) rotateY(${currentAngle}deg)`;
            updateActive();

            setTimeout(() => {
                isAnimating = false;
            }, 500);
        }, { passive: true });

        layoutRing();
    };

    // Stats Counter Animation
    const initStatsCounters = () => {
        const stats = document.querySelectorAll('.stat-number');
        if (!stats.length) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-target') || '0', 10);
                    if (target === 0) return;

                    let count = 0;
                    const duration = 2000;
                    const increment = target / (duration / 16); // 60fps

                    const timer = setInterval(() => {
                        count += increment;
                        if (count >= target) {
                            clearInterval(timer);
                            el.textContent = target;
                        } else {
                            el.textContent = Math.floor(count);
                        }
                    }, 16);

                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        stats.forEach(stat => observer.observe(stat));
    };

    // Modal Interactions
    const initTipModal = () => {
        const btn = document.getElementById('add-tip-btn');
        const modal = document.getElementById('tip-modal');
        const closeBtn = document.getElementById('close-modal');
        const submitBtn = modal?.querySelector('.submit-modal-btn');

        if (!btn || !modal) return;

        btn.addEventListener('click', () => {
            modal.classList.add('active');
        });

        closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });

        if (submitBtn) {
            submitBtn.addEventListener('click', () => {
                const form = modal.querySelector('form');
                if(!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                
                submitBtn.textContent = 'Submitted! Thank you!';
                submitBtn.style.background = '#4CAF50';
                
                setTimeout(() => {
                    modal.classList.remove('active');
                    submitBtn.textContent = 'Submit Tip to Heritage Board';
                    submitBtn.style.background = '';
                    form.reset();
                }, 1500);
            });
        }
    };

    initRevealObserver();
    initMasterclassTabs();
    initWisdomCarousel();
    initStatsCounters();
    initTipModal();
    updateYear();
    initTiltEffect();
    initPhilosophyScroll();
    initEmbersCanvas();
    initSpiceDust();
});
