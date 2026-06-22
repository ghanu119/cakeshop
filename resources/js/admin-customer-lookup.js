document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-customer-create-form]');
    if (!form) return;

    const lookupUrl = form.dataset.lookupUrl;
    const panel = document.getElementById('customer-lookup-panel');
    const submit = document.getElementById('customer-create-submit');
    const fields = form.querySelectorAll('[data-lookup-field]');

    if (!panel || !submit) return;

    let timer = null;
    let lastMatchState = panel.dataset.serverRendered === 'true';

    const renderConflict = (message, emailMatch, phoneMatch) => {
        panel.classList.remove('hidden');
        let html = `<p class="font-semibold text-amber-900">${message}</p>`;

        if (emailMatch && phoneMatch) {
            const emailLabel = emailMatch.email || 'No email';
            const phoneEmailLabel = phoneMatch.email || 'No email';
            html += `
            <p class="mt-2 text-sm text-amber-800">
                Email: ${emailMatch.name} · ${emailLabel} · ${emailMatch.phone}
            </p>
            <p class="mt-1 text-sm text-amber-800">
                Phone: ${phoneMatch.name} · ${phoneEmailLabel} · ${phoneMatch.phone}
            </p>`;
        }

        panel.innerHTML = html;
        submit.disabled = true;
        lastMatchState = true;
    };

    const renderMatch = (match) => {
        const emailLabel = match.email || 'No email';
        panel.classList.remove('hidden');
        panel.innerHTML = `
            <p class="font-semibold text-amber-900">Matching customer found</p>
            <p class="mt-1 text-sm text-amber-800">${match.name} · ${emailLabel} · ${match.phone}</p>
            <p class="mt-1 text-sm text-amber-800">${match.orders_count} orders · ${match.created_at}</p>
            <div class="mt-3 flex flex-wrap gap-3">
                <a href="${match.view_url}" class="text-sm font-medium text-indigo-700 hover:underline">View profile</a>
                <form method="post" action="${match.impersonate_url}" class="inline">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]')?.content || ''}">
                    <button type="submit" class="text-sm font-semibold text-indigo-700 hover:underline">Shop as customer</button>
                </form>
            </div>`;
        submit.disabled = true;
        lastMatchState = true;
    };

    const clearPanel = () => {
        panel.classList.add('hidden');
        panel.innerHTML = '';
        panel.removeAttribute('data-server-rendered');
        submit.disabled = false;
        lastMatchState = false;
    };

    const runLookup = () => {
        const email = form.querySelector('#email')?.value?.trim() || '';
        const phone = form.querySelector('#phone')?.value?.trim() || '';

        if (!email && !phone) {
            clearPanel();
            return;
        }

        const params = new URLSearchParams();
        if (email) params.set('email', email);
        if (phone) params.set('phone', phone);

        fetch(`${lookupUrl}?${params}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('lookup failed');
                }

                return response.json();
            })
            .then((data) => {
                panel.removeAttribute('data-lookup-warning');

                if (data.conflict) {
                    renderConflict(data.message, data.email_match, data.phone_match);
                    return;
                }

                if (data.match) {
                    renderMatch(data.match);
                    return;
                }

                clearPanel();
            })
            .catch(() => {
                if (!lastMatchState) {
                    panel.classList.add('hidden');
                    panel.innerHTML = '';
                    submit.disabled = false;
                }

                panel.dataset.lookupWarning = 'true';
            });
    };

    fields.forEach((field) => {
        field.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(runLookup, 400);
        });
    });

    if (lastMatchState) {
        submit.disabled = true;
    }

    const email = form.querySelector('#email')?.value?.trim() || '';
    const phone = form.querySelector('#phone')?.value?.trim() || '';
    if ((email || phone) && !lastMatchState) {
        runLookup();
    }
});
