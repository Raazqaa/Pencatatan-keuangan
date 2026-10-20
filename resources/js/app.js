import './bootstrap';

import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    DoughnutController,
    ArcElement,
    PointElement,
    LineController,
    LineElement,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

import { createIcons, icons } from 'lucide';

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    DoughnutController,
    ArcElement,
    PointElement,
    LineController,
    LineElement,
    Tooltip,
    Legend,
    Filler,
);

// Palet Neo-Brutalism.
export const PALETTE = {
    ink: '#121820',
    electric: '#2563EB',
    emerald: '#10B981',
    expense: '#EF4444',
    card: '#F8FAFC',
    slate: '#94A3B8',
    amber: '#F59E0B',
    violet: '#8B5CF6',
    cyan: '#06B6D4',
    pink: '#EC4899',
};

const CATEGORY_COLORS = [
    PALETTE.expense,
    PALETTE.electric,
    PALETTE.amber,
    PALETTE.violet,
    PALETTE.cyan,
    PALETTE.emerald,
    PALETTE.pink,
    PALETTE.slate,
];

export const rupiah = (value) => {
    const number = Number(value) || 0;
    const sign = number < 0 ? '-' : '';
    const abs = Math.abs(number);

    if (abs >= 1_000_000_000) {
        return `${sign}Rp${(abs / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })}M`;
    }
    if (abs >= 1_000_000) {
        return `${sign}Rp${(abs / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })}jt`;
    }
    if (abs >= 1_000) {
        return `${sign}Rp${Math.round(abs / 1_000).toLocaleString('id-ID')}rb`;
    }

    return `${sign}Rp${Math.round(abs).toLocaleString('id-ID')}`;
};

const fullRupiah = (value) => 'Rp' + Math.round(Number(value) || 0).toLocaleString('id-ID');

Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.font.weight = '600';
Chart.defaults.color = '#64748B';

const tooltipStyle = {
    backgroundColor: PALETTE.ink,
    borderColor: '#000',
    borderWidth: 3,
    cornerRadius: 10,
    padding: 12,
    titleFont: { family: "'Space Grotesk', sans-serif", size: 12, weight: '700' },
    bodyFont: { family: "'Inter', sans-serif", size: 12, weight: '600' },
    displayColors: true,
    boxPadding: 4,
};

/**
 * Bar chart pemasukan & pengeluaran bulanan.
 */
export function renderBarChart(canvas, labels, income, expense) {
    if (!canvas) return;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: income,
                    backgroundColor: PALETTE.emerald,
                    borderColor: '#000',
                    borderWidth: 2,
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 34,
                },
                {
                    label: 'Pengeluaran',
                    data: expense,
                    backgroundColor: PALETTE.expense,
                    borderColor: '#000',
                    borderWidth: 2,
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 34,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'rectRounded',
                        boxWidth: 12,
                        boxHeight: 12,
                        padding: 16,
                        font: { family: "'Space Grotesk', sans-serif", size: 11, weight: '700' },
                    },
                },
                tooltip: {
                    ...tooltipStyle,
                    callbacks: {
                        label: (ctx) => ` ${ctx.dataset.label}: ${fullRupiah(ctx.parsed.y)}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { color: '#000', width: 2 },
                    ticks: {
                        font: { family: "'Space Grotesk', sans-serif", size: 11, weight: '700' },
                        color: '#475569',
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#E2E8F0', drawTicks: false },
                    border: { display: false },
                    ticks: {
                        padding: 8,
                        font: { size: 11, weight: '600' },
                        color: '#64748B',
                        callback: (value) => rupiah(value),
                    },
                },
            },
        },
    });
}




/**
 * Doughnut chart pengeluaran per kategori.
 */
export function renderDoughnutChart(canvas, labels, values) {
    if (!canvas) return;

    const hasData = values.some((value) => Number(value) > 0);

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: hasData ? labels : ['Belum ada data'],
            datasets: [
                {
                    data: hasData ? values : [1],
                    backgroundColor: hasData
                        ? values.map((_, i) => CATEGORY_COLORS[i % CATEGORY_COLORS.length])
                        : ['#E2E8F0'],
                    borderColor: '#000',
                    borderWidth: 3,
                    hoverOffset: 10,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            animation: { duration: 700, easing: 'easeOutQuart' },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 10,
                        boxHeight: 10,
                        padding: 12,
                        font: { family: "'Space Grotesk', sans-serif", size: 11, weight: '700' },
                    },
                },
                tooltip: {
                    ...tooltipStyle,
                    callbacks: {
                        label: (ctx) => {
                            const total = ctx.dataset.data.reduce((sum, value) => sum + Number(value), 0);
                            const percent = total > 0 ? ((Number(ctx.parsed) / total) * 100).toFixed(1) : 0;
                            return ` ${ctx.label}: ${fullRupiah(ctx.parsed)} (${percent}%)`;
                        },
                    },
                },
            },
        },
    });
}


/**
 * Line chart saldo (ringkasan bulanan).
 */
export function renderBalanceChart(canvas, labels, balances) {
    if (!canvas) return;

    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Saldo',
                    data: balances,
                    borderColor: PALETTE.electric,
                    backgroundColor: 'rgba(37, 99, 235, 0.12)',
                    borderWidth: 3,
                    pointBackgroundColor: PALETTE.card,
                    pointBorderColor: '#000',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.35,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipStyle,
                    callbacks: { label: (ctx) => ` Saldo: ${fullRupiah(ctx.parsed.y)}` },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { color: '#000', width: 2 },
                    ticks: { font: { size: 10, weight: '600' }, color: '#64748B' },
                },
                y: {
                    grid: { color: '#E2E8F0', drawTicks: false },
                    border: { display: false },
                    ticks: {
                        padding: 8,
                        font: { size: 10, weight: '600' },
                        color: '#64748B',
                        callback: (value) => rupiah(value),
                    },
                },
            },
        },
    });
}


/**
 * Modal tambah / edit transaksi.
 */
function initTransactionModal() {
    const modal = document.querySelector('[data-modal="transaction"]');
    if (!modal) return;

    const form = modal.querySelector('[data-modal-form]');
    const titleEl = modal.querySelector('[data-modal-title]');
    const submitEl = modal.querySelector('[data-modal-submit]');
    const methodEl = modal.querySelector('[data-modal-method]');
    const typeSelect = modal.querySelector('[name="type"]');
    const categorySelect = modal.querySelector('[name="category"]');

    const allCategories = {
        income: JSON.parse(categorySelect?.dataset.categoriesIncome || '[]'),
        expense: JSON.parse(categorySelect?.dataset.categoriesExpense || '[]'),
    };

    const syncCategories = (selected) => {
        if (!categorySelect) return;
        const type = typeSelect?.value || 'income';
        const options = allCategories[type] || [];
        const previous = selected ?? categorySelect.value;

        categorySelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Pilih kategori';
        categorySelect.appendChild(placeholder);

        options.forEach((name) => {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            categorySelect.appendChild(option);
        });

        if (options.includes(previous)) {
            categorySelect.value = previous;
        }
    };

    const openModal = () => {
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => modal.querySelector('[name="title"]')?.focus(), 80);
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    // Tombol tambah transaksi (bisa berada di luar modal, misal di header).
    document.querySelectorAll('[data-modal-open]').forEach((button) => {
        button.addEventListener('click', () => {
            form.reset();
            methodEl.value = 'POST';
            form.action = button.dataset.action;
            titleEl.textContent = button.dataset.heading || 'Tambah Transaksi';
            submitEl.textContent = button.dataset.submitLabel || 'Simpan Transaksi';
            if (typeSelect) typeSelect.value = 'income';
            syncCategories('');
            openModal();
        });
    });

    // Tombol edit transaksi.
    document.querySelectorAll('[data-edit-transaction]').forEach((button) => {
        button.addEventListener('click', () => {
            form.action = button.dataset.action;
            methodEl.value = 'PUT';
            titleEl.textContent = 'Edit Transaksi';
            submitEl.textContent = 'Simpan Perubahan';

            const setValue = (name, value) => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) field.value = value ?? '';
            };

            setValue('title', button.dataset.title);
            setValue('amount', button.dataset.amount);
            setValue('date', button.dataset.date);
            setValue('notes', button.dataset.notes);

            if (typeSelect) typeSelect.value = button.dataset.type || 'income';
            syncCategories(button.dataset.category);

            openModal();
        });
    });

    if (typeSelect) {
        typeSelect.addEventListener('change', () => syncCategories(''));
    }

    modal.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
}

/**
 * Konfirmasi sebelum menghapus transaksi.
 */
function initDeleteConfirm() {
    document.querySelectorAll('[data-delete-transaction]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const title = button.dataset.title || 'transaksi ini';
            if (!window.confirm(`Hapus transaksi "${title}"? Tindakan ini tidak dapat dibatalkan.`)) {
                event.preventDefault();
            }
        });
    });
}

/**
 * Filter otomatis submit saat elemen berubah.
 */
function initAutoSubmit() {
    document.querySelectorAll('[data-auto-submit]').forEach((element) => {
        element.addEventListener('change', () => element.closest('form')?.submit());
    });
}

/**
 * Format input nominal dengan pemisah ribuan saat diketik.
 */
function initAmountFormat() {
    document.querySelectorAll('[data-amount-input]').forEach((input) => {
        input.addEventListener('input', () => {
            const digits = input.value.replace(/\D/g, '');
            input.value = digits ? Number(digits).toLocaleString('id-ID') : '';
        });

        input.addEventListener('focus', () => input.select());
    });

    // Hapus pemisah ribuan sebelum submit agar validasi numeric lolos.
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('[data-amount-input]').forEach((input) => {
                input.value = input.value.replace(/\D/g, '');
            });
        });
    });
}

/**
 * Notifikasi flash hilang otomatis.
 */
function initFlashDismiss() {
    document.querySelectorAll('[data-flash]').forEach((alert) => {
        const remove = () => {
            alert.style.transition = 'opacity 250ms ease, transform 250ms ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(() => alert.remove(), 260);
        };

        const timeout = setTimeout(remove, 5000);

        alert.querySelector('[data-flash-close]')?.addEventListener('click', () => {
            clearTimeout(timeout);
            remove();
        });
    });
}

/**
 * Konfirmasi sebelum menghapus kategori.
 */
function initCategoryDeleteConfirm() {
    document.querySelectorAll('[data-delete-category]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const title = button.dataset.title || 'kategori ini';
            const used = Number(button.dataset.used || 0);

            const message = used > 0
                ? `Hapus kategori "${title}"? ${used} transaksi lama tetap memakai nama kategori ini.`
                : `Hapus kategori "${title}"?`;

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

/**
 * Grafik diinisialisasi dari data-* yang diserialisasi server.
 */
function initCharts() {
    const barCanvas = document.querySelector('[data-chart="bar"]');
    if (barCanvas) {
        renderBarChart(
            barCanvas,
            JSON.parse(barCanvas.dataset.labels || '[]'),
            JSON.parse(barCanvas.dataset.income || '[]'),
            JSON.parse(barCanvas.dataset.expense || '[]'),
        );
    }

    document.querySelectorAll('[data-chart="doughnut"]').forEach((canvas) => {
        renderDoughnutChart(
            canvas,
            JSON.parse(canvas.dataset.labels || '[]'),
            JSON.parse(canvas.dataset.values || '[]'),
        );
    });

    const balanceCanvas = document.querySelector('[data-chart="balance"]');
    if (balanceCanvas) {
        renderBalanceChart(
            balanceCanvas,
            JSON.parse(balanceCanvas.dataset.labels || '[]'),
            JSON.parse(balanceCanvas.dataset.balances || '[]'),
        );
    }
}

/**
 * Sidebar untuk tampilan mobile.
 */
function initMobileSidebar() {
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');

    if (!toggle || !sidebar) return;

    const open = () => {
        sidebar.classList.add('is-open');
        overlay?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        sidebar.classList.remove('is-open');
        overlay?.classList.add('hidden');
        document.body.style.overflow = '';
    };

    toggle.addEventListener('click', () => {
        sidebar.classList.contains('is-open') ? close() : open();
    });

    overlay?.addEventListener('click', close);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
}


/**
 * Ikon Lucide digambar ulang setiap DOM siap.
 */
function initIcons() {
    createIcons({ icons, attrs: { 'stroke-width': 2.25 } });
}

document.addEventListener('DOMContentLoaded', () => {
    initIcons();
    initCharts();
    initTransactionModal();
    initDeleteConfirm();
    initCategoryDeleteConfirm();
    initAutoSubmit();
    initAmountFormat();
    initFlashDismiss();
    initMobileSidebar();
});
