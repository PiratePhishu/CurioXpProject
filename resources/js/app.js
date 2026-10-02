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

window.curioXp = { sendJSON, setSaveState };
