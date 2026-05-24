let cleanupFns = [];
let embersRafId = null;

function onEvent(target, event, handler, options) {
    target.addEventListener(event, handler, options);
    cleanupFns.push(() => target.removeEventListener(event, handler, options));
}

export function initTips() {
    // Clean up previous run
    if (embersRafId) {
        cancelAnimationFrame(embersRafId);
        embersRafId = null;
    }
    cleanupFns.forEach(fn => fn());
    cleanupFns = [];
    document.querySelectorAll('.spice-dust').forEach(el => el.remove());

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
            const onMove = (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = ((y - centerY) / centerY) * -10;
                const rotateY = ((x - centerX) / centerX) * 10;
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
            };
            const onLeave = () => {
                card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
                card.style.transition = 'box-shadow 0.4s ease, border-color 0.4s ease, transform 0.5s ease-out';
            };
            const onEnter = () => {
                card.style.transition = 'box-shadow 0.4s ease, border-color 0.4s ease, transform 0.05s linear';
            };
            card.addEventListener('mousemove', onMove);
            card.addEventListener('mouseleave', onLeave);
            card.addEventListener('mouseenter', onEnter);
            cleanupFns.push(() => {
                card.removeEventListener('mousemove', onMove);
                card.removeEventListener('mouseleave', onLeave);
                card.removeEventListener('mouseenter', onEnter);
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

        const observerOptions = {
            root: null,
            rootMargin: '-50% 0px -50% 0px',
            threshold: 0
        };

        const updatePhilosophyUI = (newIndex) => {
            if (newIndex === activeIndex) return;

            const oldCard = cards[activeIndex];
            const newCard = cards[newIndex];

            if (oldCard) oldCard.classList.remove('active');
            if (newCard) newCard.classList.add('active');

            images.forEach((img, i) => {
                if (i === newIndex) img.classList.add('active');
                else img.classList.remove('active');
            });

            textWrapper.classList.remove('content-fade-in');
            textWrapper.classList.add('content-fade-out');

            setTimeout(() => {
                const newTitle = newCard.getAttribute('data-title');
                const newTip = newCard.getAttribute('data-tip');

                if (titleTarget) titleTarget.textContent = newTitle;
                if (tipTarget) tipTarget.innerHTML = `<strong>The Tip:</strong> ${newTip}`;

                textWrapper.classList.remove('content-fade-out');
                void textWrapper.offsetWidth;
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
        cleanupFns.push(() => observer.disconnect());

        const onScroll = () => {
            const rect = track.getBoundingClientRect();
            const maxScroll = rect.height - window.innerHeight;
            let progress = Math.abs(rect.top) / maxScroll;
            if (rect.top > 0) progress = 0;
            if (progress > 1) progress = 1;

            if (progressBar) {
                progressBar.style.transform = `scaleY(${progress})`;
            }
        };
        onEvent(window, 'scroll', onScroll);
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

        onEvent(window, 'resize', resize);
        resize();

        class Particle {
            constructor() {
                this.reset();
                this.y = Math.random() * height;
            }

            reset() {
                this.x = Math.random() * width;
                this.y = height + Math.random() * 50;
                this.size = Math.random() * 2.5 + 1;
                this.speedX = Math.random() * 1.5 - 0.75;
                this.speedY = Math.random() * -1.5 - 0.5;
                this.life = Math.random() * 0.6 + 0.2;
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
            if (!document.getElementById('embers-canvas')) return;
            ctx.clearRect(0, 0, width, height);
            ctx.globalCompositeOperation = 'screen';

            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();
            }
            embersRafId = requestAnimationFrame(animate);
        };

        animate();
    };

    // Spice Dust Cursor Trail
    const initSpiceDust = () => {
        const section = document.querySelector('.grandma-section');
        if (!section) return;

        let isThrottled = false;
        const onMove = (e) => {
            if (isThrottled) return;
            isThrottled = true;
            setTimeout(() => isThrottled = false, 40);

            const dust = document.createElement('div');
            dust.className = 'spice-dust';
            dust.style.left = (e.clientX + (Math.random() * 20 - 10)) + 'px';
            dust.style.top = (e.clientY + (Math.random() * 20 - 10)) + 'px';

            const colors = ['#b71c1c', '#d32f2f', '#f57f17', '#8d6e63', '#4e342e'];
            dust.style.background = colors[Math.floor(Math.random() * colors.length)];

            document.body.appendChild(dust);

            requestAnimationFrame(() => {
                dust.style.transform = `translateY(${Math.random() * 40 + 20}px) rotate(${Math.random() * 90}deg)`;
                dust.style.opacity = '0';
            });

            setTimeout(() => {
                if (dust.parentNode) dust.remove();
            }, 1000);
        };
        section.addEventListener('mousemove', onMove);
        cleanupFns.push(() => section.removeEventListener('mousemove', onMove));
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
        const radius = Math.round((400 / 2) / Math.tan(Math.PI / Math.max(numCards, 3)));
        let currentAngle = 0;
        let activeIndex = 0;
        let isAnimating = false;

        if (totalEl) totalEl.textContent = numCards;

        const layoutRing = () => {
            cards.forEach((card, i) => {
                const angle = i * angleStep;
                card.style.transform = `rotateY(${angle}deg) translateZ(${radius}px)`;
            });
            ring.style.transform = `translateZ(-${radius}px) rotateY(${currentAngle}deg)`;
            updateActive();
        };

        const updateActive = () => {
            let normalizedAngle = (((-currentAngle) % 360) + 360) % 360;
            activeIndex = Math.round(normalizedAngle / angleStep) % numCards;

            cards.forEach((card, i) => {
                card.classList.toggle('active', i === activeIndex);
            });
            if (currentEl) currentEl.textContent = activeIndex + 1;
        };

        const onWheel = (e) => {
            e.preventDefault();
            if (isAnimating) return;

            const isHorizontal = Math.abs(e.deltaX) > Math.abs(e.deltaY);
            const delta = isHorizontal ? e.deltaX : e.deltaY;

            if (Math.abs(delta) < 15) return;

            isAnimating = true;

            const direction = delta > 0 ? -1 : 1;
            currentAngle += direction * angleStep;
            ring.style.transform = `translateZ(-${radius}px) rotateY(${currentAngle}deg)`;
            updateActive();

            setTimeout(() => {
                isAnimating = false;
            }, 500);
        };
        viewport.addEventListener('wheel', onWheel, { passive: false });
        cleanupFns.push(() => viewport.removeEventListener('wheel', onWheel));

        let touchStartX = 0;
        let touchStartY = 0;
        const onTouchStart = (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        };
        const onTouchEnd = (e) => {
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
        };
        viewport.addEventListener('touchstart', onTouchStart, { passive: true });
        viewport.addEventListener('touchend', onTouchEnd, { passive: true });
        cleanupFns.push(() => {
            viewport.removeEventListener('touchstart', onTouchStart);
            viewport.removeEventListener('touchend', onTouchEnd);
        });

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
                    const increment = target / (duration / 16);

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
        cleanupFns.push(() => observer.disconnect());
    };

    const escapeHtml = (str) => {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    };

    const prependCommunityTip = (authorName, content, id) => {
        const list = document.getElementById('community-tips-list');
        const empty = document.getElementById('community-tips-empty');
        if (!list) return;

        if (empty) empty.remove();

        const article = document.createElement('article');
        article.className = 'community-tip-card community-tip-new';
        article.dataset.tipId = String(id ?? '');
        article.innerHTML = `
            <blockquote class="wisdom-quote">${escapeHtml(content)}</blockquote>
            <p class="community-tip-author">— ${escapeHtml(authorName)}</p>
        `;
        list.prepend(article);

        const board = document.getElementById('community-tips-board');
        if (board) {
            board.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    };

    // Modal Interactions
    const initTipModal = () => {
        const btn = document.getElementById('add-tip-btn');
        const modal = document.getElementById('tip-modal');
        const closeBtn = document.getElementById('close-modal');
        const form = document.getElementById('tip-form');
        const errorEl = document.getElementById('tip-form-error');

        if (!btn || !modal || !form) return;

        const openModal = () => {
            modal.classList.add('active');
            if (errorEl) {
                errorEl.hidden = true;
                errorEl.textContent = '';
            }
        };
        const closeModal = () => modal.classList.remove('active');
        const onBackdrop = (e) => {
            if (e.target === modal) modal.classList.remove('active');
        };

        btn.addEventListener('click', openModal);
        closeBtn?.addEventListener('click', closeModal);
        modal.addEventListener('click', onBackdrop);

        const onSubmit = async (e) => {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const authorName = document.getElementById('tip-author')?.value.trim() ?? '';
            const content = document.getElementById('tip-content')?.value.trim() ?? '';
            const submitBtn = form.querySelector('.submit-modal-btn');
            if (!submitBtn) return;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            if (errorEl) errorEl.hidden = true;

            try {
                const response = await fetch('/api/tips', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ authorName, content }),
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Could not save your tip');
                }

                const wasPending = Boolean(data.pending);
                if (wasPending) {
                    submitBtn.textContent = 'Sent for review!';
                    if (errorEl) {
                        errorEl.textContent = data.message || 'Your tip will appear after admin approval.';
                        errorEl.hidden = false;
                        errorEl.style.color = '#2e7d32';
                        errorEl.style.background = '#e8f5e9';
                        errorEl.style.padding = '0.75rem';
                        errorEl.style.borderRadius = '8px';
                    }
                } else if (data.tip) {
                    prependCommunityTip(data.tip.authorName, data.tip.content, data.tip.id);
                    submitBtn.textContent = 'Submitted! Thank you!';
                    submitBtn.style.background = '#4CAF50';
                }

                setTimeout(() => {
                    closeModal();
                    submitBtn.textContent = 'Submit Tip to Heritage Board';
                    submitBtn.style.background = '';
                    submitBtn.disabled = false;
                    form.reset();
                    if (errorEl) {
                        errorEl.hidden = true;
                        errorEl.style.color = '';
                        errorEl.style.background = '';
                        errorEl.style.padding = '';
                    }
                }, wasPending ? 2500 : 1200);
            } catch (err) {
                if (errorEl) {
                    errorEl.textContent = err.message || 'Something went wrong. Please try again.';
                    errorEl.hidden = false;
                }
                submitBtn.textContent = 'Submit Tip to Heritage Board';
                submitBtn.disabled = false;
            }
        };

        form.addEventListener('submit', onSubmit);
        cleanupFns.push(() => {
            btn.removeEventListener('click', openModal);
            closeBtn?.removeEventListener('click', closeModal);
            modal.removeEventListener('click', onBackdrop);
            form.removeEventListener('submit', onSubmit);
        });
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
}
