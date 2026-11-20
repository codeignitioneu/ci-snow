/*!
 * Seasonal Snow by Code Ignition – https://github.com/codeignitioneu/ci-snow
 * Canvas animation: snowflakes, Christmas baubles, tiny gifts, soft dots and custom images.
 */
(function () {
	'use strict';

	function rnd(a, b) { return a + Math.random() * (b - a); }
	function pick(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

	function shade(hex, amt) {
		var n = parseInt(hex.slice(1), 16);
		var r = Math.max(0, Math.min(255, (n >> 16) + amt));
		var g = Math.max(0, Math.min(255, ((n >> 8) & 255) + amt));
		var b = Math.max(0, Math.min(255, (n & 255) + amt));
		return 'rgb(' + r + ',' + g + ',' + b + ')';
	}

	function drawFlake(x, s, cfg) {
		var r = s / 2;
		x.lineCap = 'round';
		for (var pass = 0; pass < 2; pass++) {
			x.strokeStyle = pass === 0 ? cfg.outlineColor : cfg.flakeColor;
			x.lineWidth = pass === 0 ? Math.max(2.4, s / 7) : Math.max(1.2, s / 13);
			for (var i = 0; i < 6; i++) {
				x.save();
				x.rotate(i * Math.PI / 3);
				x.beginPath();
				x.moveTo(0, 0); x.lineTo(0, -r);
				x.moveTo(0, -r * 0.55); x.lineTo(-r * 0.28, -r * 0.8);
				x.moveTo(0, -r * 0.55); x.lineTo(r * 0.28, -r * 0.8);
				x.stroke();
				x.restore();
			}
		}
	}

	function drawBauble(x, s, color) {
		var r = s / 2;
		var g = x.createRadialGradient(-r * 0.35, -r * 0.35, r * 0.1, 0, 0, r);
		g.addColorStop(0, shade(color, 90));
		g.addColorStop(0.45, color);
		g.addColorStop(1, shade(color, -60));
		x.fillStyle = g;
		x.beginPath(); x.arc(0, r * 0.15, r * 0.85, 0, 6.2832); x.fill();
		x.fillStyle = '#c9a227';
		x.fillRect(-r * 0.22, -r * 0.82, r * 0.44, r * 0.2);
		x.strokeStyle = '#c9a227'; x.lineWidth = Math.max(1, s / 18);
		x.beginPath(); x.arc(0, -r * 0.95, r * 0.13, 0, 6.2832); x.stroke();
		x.fillStyle = 'rgba(255,255,255,0.7)';
		x.beginPath(); x.ellipse(-r * 0.3, -r * 0.15, r * 0.14, r * 0.24, -0.6, 0, 6.2832); x.fill();
	}

	function drawGift(x, s, color) {
		var h = s * 0.8, w = s * 0.8;
		x.fillStyle = color;
		x.fillRect(-w / 2, -h / 2 + s * 0.12, w, h * 0.85);
		x.fillStyle = shade(color, 35);
		x.fillRect(-w / 2 - s * 0.05, -h / 2, w + s * 0.1, s * 0.2);
		x.fillStyle = '#ffd54f';
		x.fillRect(-s * 0.07, -h / 2, s * 0.14, h * 0.97);
		x.fillRect(-w / 2, -h / 2 + s * 0.42, w, s * 0.1);
		x.strokeStyle = '#ffd54f'; x.lineWidth = Math.max(1, s / 12);
		x.beginPath();
		x.ellipse(-s * 0.13, -h / 2 - s * 0.08, s * 0.13, s * 0.08, -0.5, 0, 6.2832);
		x.ellipse(s * 0.13, -h / 2 - s * 0.08, s * 0.13, s * 0.08, 0.5, 0, 6.2832);
		x.stroke();
	}

	function drawDot(x, s, cfg) {
		var r = s / 4;
		x.fillStyle = cfg.flakeColor;
		x.strokeStyle = cfg.outlineColor;
		x.lineWidth = 0.8;
		x.beginPath(); x.arc(0, 0, r, 0, 6.2832); x.fill(); x.stroke();
	}

	function start(canvas, cfg, isPreview) {
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduce && !isPreview) { return; }

		var host = isPreview ? canvas.parentNode : window;
		var mobile = !isPreview && window.innerWidth < 768;
		if (mobile && !cfg.mobileEnabled) { return; }

		var kinds = (cfg.shapes || []).slice();
		var imgs = [];
		(cfg.images || []).forEach(function (src) {
			var im = new Image();
			im.src = src;
			imgs.push(im);
		});
		if (imgs.length) { kinds.push('image'); }
		if (!kinds.length) { return; }

		var ctx = canvas.getContext('2d');
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var W = 0, H = 0;
		function resize() {
			W = isPreview ? host.clientWidth : window.innerWidth;
			H = isPreview ? host.clientHeight : window.innerHeight;
			canvas.width = W * dpr; canvas.height = H * dpr;
			canvas.style.width = W + 'px'; canvas.style.height = H + 'px';
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
		}
		resize();
		window.addEventListener('resize', resize);

		var count = isPreview ? 30 : (mobile ? cfg.countMobile : cfg.countDesktop);
		var items = [];
		function make(top) {
			var kind = pick(kinds);
			var size = rnd(cfg.sizeMin, cfg.sizeMax);
			if (kind === 'flake') { size *= 1.1; }
			return {
				kind: kind,
				x: rnd(0, W),
				y: top ? rnd(-H * 0.2, -10) : rnd(-H, H),
				s: size,
				vy: rnd(0.5, 1.3) * cfg.speed * (0.6 + size / 40),
				sway: rnd(0, 6.28),
				swaySpeed: rnd(0.008, 0.02),
				rot: rnd(0, 6.28),
				vr: rnd(-0.015, 0.015),
				color: kind === 'bauble' ? pick(cfg.baubleColors) : (kind === 'gift' ? pick(cfg.giftColors) : null),
				img: kind === 'image' ? pick(imgs) : null
			};
		}
		for (var i = 0; i < count; i++) { items.push(make(false)); }

		var running = true;
		document.addEventListener('visibilitychange', function () {
			running = !document.hidden;
			if (running) { requestAnimationFrame(frame); }
		});

		function frame() {
			if (!running) { return; }
			ctx.clearRect(0, 0, W, H);
			ctx.globalAlpha = cfg.opacity;
			for (var i = 0; i < items.length; i++) {
				var p = items[i];
				p.y += p.vy;
				p.sway += p.swaySpeed;
				p.x += Math.sin(p.sway) * 0.6;
				p.rot += p.vr;
				if (p.y > H + 40) { items[i] = make(true); continue; }
				ctx.save();
				ctx.translate(p.x, p.y);
				if (p.kind === 'bauble') {
					ctx.rotate(Math.sin(p.sway) * 0.35);
					drawBauble(ctx, p.s, p.color);
				} else if (p.kind === 'gift') {
					ctx.rotate(p.rot * 0.6);
					drawGift(ctx, p.s, p.color);
				} else if (p.kind === 'flake') {
					ctx.rotate(p.rot);
					drawFlake(ctx, p.s, cfg);
				} else if (p.kind === 'dot') {
					drawDot(ctx, p.s, cfg);
				} else if (p.img && p.img.complete && p.img.naturalWidth) {
					ctx.rotate(p.rot * 0.5);
					var ratio = p.img.naturalHeight / p.img.naturalWidth;
					ctx.drawImage(p.img, -p.s / 2, -p.s * ratio / 2, p.s, p.s * ratio);
				}
				ctx.restore();
			}
			requestAnimationFrame(frame);
		}
		requestAnimationFrame(frame);
	}

	function boot() {
		if (window.ciSnowConfig) {
			var c = document.createElement('canvas');
			c.className = 'ci-snow-canvas';
			c.setAttribute('aria-hidden', 'true');
			c.style.cssText = 'position:fixed;left:0;top:0;pointer-events:none;z-index:' + (window.ciSnowConfig.zIndex || 9990);
			document.body.appendChild(c);
			start(c, window.ciSnowConfig, false);
		}
		var prev = document.getElementById('ci-snow-preview');
		if (prev && window.ciSnowPreview) {
			var pc = document.createElement('canvas');
			pc.style.cssText = 'position:absolute;left:0;top:0;pointer-events:none';
			prev.appendChild(pc);
			start(pc, window.ciSnowPreview, true);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
