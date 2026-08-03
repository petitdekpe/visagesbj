import { Controller } from '@hotwired/stimulus';

/*
 * Infinite archive-photo wall: a virtualized 2D tile grid you can scroll
 * endlessly in any direction (up/down/left/right), recycling the same small
 * photo pool forever — there is no "end" to reach.
 *
 * How it works: the element itself is a native scroll container (overflow:
 * auto) holding a large-but-finite "world" div. Cells are plain <figure>s,
 * positioned with `transform: translate3d()` on a fixed lattice — GPU-
 * composited, no layout reflow — and created/removed as they enter/leave the
 * viewport (+ a small buffer) so DOM size stays bounded no matter how long
 * someone scrolls. Which photo appears in cell (row, col) is decided by a
 * deterministic hash, so revisiting a spot always shows the same photo, but
 * nearby cells don't look like an obvious repeating tile.
 *
 * True infinite scroll needs the world to never run out in any direction,
 * but an unbounded-size element would eventually hit browser/perf limits.
 * Instead, the world is a large fixed square; whenever scroll position gets
 * close to one of its edges, it's silently recentered. Cells never move once
 * created — instead the *world itself* gets an offsetting transform, a
 * single composited write regardless of how many cells are on screen —  so
 * nothing visibly jumps and recentering stays cheap even with lots of tiles.
 *
 * Every cell is a link to that personality's page. Rows alternate a
 * half-cell horizontal offset (brick/subway-tile pattern) purely for visual
 * variety — cell *size* stays uniform on purpose, since that's what keeps
 * the row/col → pixel math (and therefore the virtualization) O(1).
 */
const CELL_SIZE = 240;
const CELL_SIZE_MOBILE = 160;
const MOBILE_BREAKPOINT = 480;
const GAP = 10;
const WORLD_SIZE = 20000;
const RENDER_BUFFER_CELLS = 2;
const RECENTER_MARGIN_CELLS = 6;

export default class extends Controller {
    static values = { photos: Array };

    connect() {
        this.photos = this.photosValue;

        if (!Array.isArray(this.photos) || this.photos.length === 0) {
            return;
        }

        this.originX = 0;
        this.originY = 0;
        this.cells = new Map();
        this.pendingFrame = null;

        this.world = document.createElement('div');
        this.world.className = 'gallery-world';
        this.world.style.width = `${WORLD_SIZE}px`;
        this.world.style.height = `${WORLD_SIZE}px`;
        this.element.appendChild(this.world);

        this.applyCellSize(this.currentCellSize());

        // Start centered so there's room to scroll in all four directions immediately.
        this.element.scrollLeft = (WORLD_SIZE - this.element.clientWidth) / 2;
        this.element.scrollTop = (WORLD_SIZE - this.element.clientHeight) / 2;

        this.onScroll = () => this.scheduleFrame();
        this.onResize = () => this.handleResize();
        this.element.addEventListener('scroll', this.onScroll, { passive: true });
        window.addEventListener('resize', this.onResize);

        this.render();
    }

    disconnect() {
        this.element.removeEventListener('scroll', this.onScroll);
        window.removeEventListener('resize', this.onResize);
        if (this.pendingFrame) {
            cancelAnimationFrame(this.pendingFrame);
        }
    }

    currentCellSize() {
        return window.innerWidth < MOBILE_BREAKPOINT ? CELL_SIZE_MOBILE : CELL_SIZE;
    }

    applyCellSize(size) {
        this.cellSize = size;
        this.recenterMargin = size * RECENTER_MARGIN_CELLS;
        this.renderBuffer = size * RENDER_BUFFER_CELLS;
    }

    handleResize() {
        const size = this.currentCellSize();

        if (size !== this.cellSize) {
            // Cell size change invalidates every existing tile's grid math —
            // simplest correct fix is to drop and let render() rebuild.
            this.cells.forEach((cellEl) => cellEl.remove());
            this.cells.clear();
            this.applyCellSize(size);
        }

        this.scheduleFrame();
    }

    scheduleFrame() {
        if (this.pendingFrame) {
            return;
        }

        this.pendingFrame = requestAnimationFrame(() => {
            this.pendingFrame = null;
            this.recenterIfNeeded();
            this.render();
        });
    }

    recenterIfNeeded() {
        const el = this.element;
        const center = WORLD_SIZE / 2;
        let deltaX = 0;
        let deltaY = 0;

        if (el.scrollLeft < this.recenterMargin || el.scrollLeft > WORLD_SIZE - el.clientWidth - this.recenterMargin) {
            deltaX = center - el.scrollLeft;
            el.scrollLeft = center;
        }

        if (el.scrollTop < this.recenterMargin || el.scrollTop > WORLD_SIZE - el.clientHeight - this.recenterMargin) {
            deltaY = center - el.scrollTop;
            el.scrollTop = center;
        }

        if (deltaX === 0 && deltaY === 0) {
            return;
        }

        // Cells keep their original transform; the world's own transform
        // absorbs the jump instead — one composited write, however many
        // tiles are currently rendered.
        this.originX -= deltaX;
        this.originY -= deltaY;
        this.world.style.transform = `translate3d(${-this.originX}px, ${-this.originY}px, 0)`;
    }

    render() {
        const el = this.element;
        const size = this.cellSize;

        const left = el.scrollLeft - this.renderBuffer + this.originX;
        const top = el.scrollTop - this.renderBuffer + this.originY;
        const right = el.scrollLeft + el.clientWidth + this.renderBuffer + this.originX;
        const bottom = el.scrollTop + el.clientHeight + this.renderBuffer + this.originY;

        const colStart = Math.floor(left / size);
        const colEnd = Math.floor(right / size);
        const rowStart = Math.floor(top / size);
        const rowEnd = Math.floor(bottom / size);

        const needed = new Set();

        for (let row = rowStart; row <= rowEnd; row++) {
            for (let col = colStart; col <= colEnd; col++) {
                const key = `${row},${col}`;
                needed.add(key);

                if (!this.cells.has(key)) {
                    this.cells.set(key, this.createCell(row, col));
                }
            }
        }

        this.cells.forEach((cellEl, key) => {
            if (!needed.has(key)) {
                cellEl.remove();
                this.cells.delete(key);
            }
        });
    }

    createCell(row, col) {
        const size = this.cellSize;
        const photo = this.photos[this.hash(row, col) % this.photos.length];
        // row % 2 can be -1 for negative rows in JS — abs() normalizes that.
        const staggerX = Math.abs(row % 2) === 1 ? size / 2 : 0;

        const figure = document.createElement('figure');
        figure.className = 'gallery-cell';
        figure.style.width = `${size - GAP}px`;
        figure.style.height = `${size - GAP}px`;
        figure.style.transform = `translate3d(${col * size + staggerX + GAP / 2}px, ${row * size + GAP / 2}px, 0)`;

        const link = document.createElement('a');
        link.className = 'gallery-cell__link';
        link.href = photo.url;

        const img = document.createElement('img');
        img.src = photo.imageUrl;
        // Empty alt: the figcaption (still read by screen readers despite
        // being visually hidden until hover) already names the link.
        img.alt = '';
        img.loading = 'lazy';
        img.decoding = 'async';
        img.draggable = false;

        const figcaption = document.createElement('figcaption');
        figcaption.textContent = photo.caption;

        link.append(img, figcaption);
        figure.append(link);
        this.world.appendChild(figure);

        return figure;
    }

    /**
     * Deterministic pseudo-random pick per (row, col) — same spot always
     * shows the same photo, but the pattern doesn't look like an obvious
     * repeating tile the way a plain modulo would.
     */
    hash(row, col) {
        let h = Math.imul(row, 374761393) ^ Math.imul(col, 668265263);
        h = Math.imul(h ^ (h >>> 13), 2246822519);
        h = Math.imul(h ^ (h >>> 16), 3266489917);
        h ^= h >>> 16;

        return Math.abs(h);
    }
}
