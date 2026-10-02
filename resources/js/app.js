const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function setSaveState(state) {
    const dot = document.getElementById('saveDot');
    const text = document.getElementById('saveText');

    if (!dot || !text) {
        return;
    }

    const states = {
        idle: { dot: '#2E7D32', label: 'Opgeslagen' },
        saving: { dot: '#F7941E', label: 'Opslaan…' },
        error: { dot: '#C62828', label: 'Opslaan mislukt' },
    };

    const { dot: color, label } = states[state] ?? states.idle;
    dot.style.color = color;
    text.textContent = label;
}

async function sendJSON(url, method, body) {
    setSaveState('saving');

    try {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
            body: JSON.stringify(body),
        });

        if (!response.ok) {
            throw new Error('Request failed');
        }

        const data = await response.json();
        setSaveState('idle');

        return data;
    } catch (error) {
        setSaveState('error');
        throw error;
    }
}

function filterRows(input, rowSelector, emptyRowSelector) {
    const rows = document.querySelectorAll(rowSelector);
    const emptyRow = emptyRowSelector ? document.querySelector(emptyRowSelector) : null;

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach((row) => {
            const matches = (row.dataset.search ?? '').includes(query);
            row.classList.toggle('hidden', !matches);
            if (matches) {
                visibleCount += 1;
            }
        });

        emptyRow?.classList.toggle('hidden', visibleCount !== 0 || query === '');
    });
}

window.curioXp = { sendJSON, setSaveState, filterRows };
