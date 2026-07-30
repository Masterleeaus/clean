const root = document.querySelector('[data-titan-operations-root]');

if (root) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const jsonRequest = async (url, options = {}) => {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                ...(options.headers ?? {}),
            },
            ...options,
        });
        const body = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = body?.error?.message ?? body?.message ?? 'Titan request failed.';
            throw new Error(message);
        }
        return body;
    };

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const renderSummary = (body) => {
        const counts = body?.data?.counts ?? {};
        Object.entries(counts).forEach(([key, value]) => {
            const element = root.querySelector(`[data-titan-count="${key}"]`);
            if (element) element.textContent = value === null ? 'Unavailable' : Number(value).toLocaleString();
        });

        const creative = body?.data?.creative_marketing ?? {};
        Object.entries(creative).forEach(([key, value]) => {
            const element = root.querySelector(`[data-titan-creative-count="${key}"]`);
            if (element) element.textContent = value === null ? 'Unavailable' : Number(value).toLocaleString('en-AU');
        });
        const assurance = body?.data?.assurance ?? {};
        Object.entries(assurance).forEach(([key, value]) => {
            const element = root.querySelector(`[data-titan-assurance-count="${key}"]`);
            if (element) element.textContent = value === null ? 'Unavailable' : Number(value).toLocaleString();
        });
        const accommodation = body?.data?.property_accommodation ?? {};
        Object.entries(accommodation).forEach(([key, value]) => {
            const element = root.querySelector(`[data-titan-accommodation-count="${key}"]`);
            if (element) element.textContent = value === null ? 'Unavailable' : Number(value).toLocaleString();
        });
        const renderMoney = (value, currency = 'AUD') => value === null
            ? 'Unavailable'
            : new Intl.NumberFormat('en-AU', { style: 'currency', currency }).format(Number(value) / 100);
        const intelligence = body?.data?.intelligence_runtime ?? {};
        Object.entries(intelligence).forEach(([key, value]) => {
            const element = root.querySelector(`[data-titan-intelligence-count="${key}"]`);
            if (element) element.textContent = value === null ? 'Unavailable' : Number(value).toLocaleString('en-AU');
        });
        const finance = body?.data?.finance ?? {};
        Object.entries(finance).forEach(([key, value]) => {
            const element = root.querySelector(`[data-titan-finance-count="${key}"]`);
            if (element) element.textContent = key.endsWith('_minor') ? renderMoney(value, finance.currency ?? 'AUD') : (value === null ? 'Unavailable' : Number(value).toLocaleString('en-AU'));
        });
        const payments = body?.data?.payments ?? {};
        Object.entries(payments).forEach(([key, value]) => {
            const element = root.querySelector(`[data-titan-payment-count="${key}"]`);
            if (element) element.textContent = value === null ? 'Unavailable' : Number(value).toLocaleString('en-AU');
        });
        const trust = body?.data?.trust_accounting ?? {};
        Object.entries(trust).forEach(([key, value]) => {
            const element = root.querySelector(`[data-titan-trust-count="${key}"]`);
            if (element) element.textContent = key.endsWith('_minor') ? renderMoney(value, trust.currency ?? 'AUD') : (value === null ? 'Unavailable' : Number(value).toLocaleString('en-AU'));
        });
    };

    const renderCapabilities = (body) => {
        const data = body?.data ?? {};
        const capabilities = Object.values(data.capabilities ?? {});
        const tools = Object.values(data.workcore_tools ?? {});
        const list = root.querySelector('[data-titan-capability-list]');
        const count = root.querySelector('[data-titan-capability-count]');
        const extensions = root.querySelector('[data-titan-extension-list]');
        if (count) count.textContent = `${capabilities.length + tools.length} registered`;
        if (list) {
            list.innerHTML = [...capabilities, ...tools].slice(0, 20).map((item) => {
                const id = item.id ?? item.key ?? 'unnamed capability';
                const provider = item.provider ?? item.domain ?? 'WorkCore';
                return `<div class="titan-list-row"><strong>${escapeHtml(id)}</strong><span>${escapeHtml(provider)}</span></div>`;
            }).join('') || '<p class="titan-muted">No executable capabilities are currently registered.</p>';
        }
        if (extensions) {
            extensions.innerHTML = (data.extensions ?? []).map((extension) => `
                <div class="titan-extension-row">
                    <div><strong>${escapeHtml(extension.name ?? extension.id)}</strong><span>${escapeHtml(extension.version ?? '')}</span></div>
                    <span class="titan-status-pill ${extension.enabled && extension.compatible ? 'is-ready' : ''}">${escapeHtml(extension.status ?? 'unknown')}</span>
                </div>
            `).join('');
        }
    };

    const companySelect = root.querySelector('[data-titan-company-select]');
    companySelect?.addEventListener('change', async () => {
        companySelect.disabled = true;
        try {
            await jsonRequest('/api/titan/context/switch', {
                method: 'POST',
                body: JSON.stringify({ target_company_id: Number(companySelect.value) }),
            });
            window.location.reload();
        } catch (error) {
            window.alert(error.message);
            companySelect.disabled = false;
        }
    });

    Promise.all([
        jsonRequest('/api/titan/summary').then(renderSummary),
        jsonRequest('/api/titan/capabilities').then(renderCapabilities),
    ]).catch((error) => {
        const panel = root.querySelector('[data-titan-capabilities]');
        if (panel) panel.insertAdjacentHTML('beforeend', `<p class="titan-notice titan-notice-error">${escapeHtml(error.message)}</p>`);
    });

    const mapsForm = root.querySelector('[data-titan-maps-search]');
    const mapsProgress = root.querySelector('[data-titan-maps-progress]');
    const mapsCandidates = root.querySelector('[data-titan-maps-candidates]');
    let pollTimer = null;

    const loadCandidates = async (searchId) => {
        const body = await jsonRequest(`/api/titan/maps-intelligence/candidates?search_id=${encodeURIComponent(searchId)}&per_page=50`);
        const candidates = body?.data ?? [];
        if (!mapsCandidates) return;
        mapsCandidates.innerHTML = candidates.map((candidate) => {
            const place = candidate.place ?? {};
            return `<article class="titan-candidate-card">
                <div><p class="titan-eyebrow">${escapeHtml(candidate.candidate_type ?? 'unclassified')}</p><h3>${escapeHtml(place.name ?? 'Unnamed candidate')}</h3></div>
                <p>${escapeHtml(place.address ?? '')}</p>
                <dl><div><dt>Phone</dt><dd>${escapeHtml(place.phone ?? 'Not listed')}</dd></div><div><dt>Rating</dt><dd>${escapeHtml(place.rating ?? '—')}</dd></div><div><dt>Review</dt><dd>${escapeHtml(candidate.review_status ?? 'unreviewed')}</dd></div></dl>
            </article>`;
        }).join('') || '<p class="titan-muted">No candidates have been staged yet.</p>';
    };

    const pollSearch = async (searchId) => {
        const body = await jsonRequest(`/api/titan/maps-intelligence/searches/${encodeURIComponent(searchId)}`);
        const search = body?.data ?? body;
        if (mapsProgress) {
            mapsProgress.innerHTML = `<div class="titan-list-row"><strong>${escapeHtml(search.query ?? 'Discovery search')}</strong><span>${escapeHtml(search.status ?? 'queued')}</span></div>`;
        }
        await loadCandidates(searchId);
        if (!['completed', 'failed', 'cancelled', 'partially_completed'].includes(String(search.status ?? '').toLowerCase())) {
            pollTimer = window.setTimeout(() => pollSearch(searchId).catch((error) => {
                if (mapsProgress) mapsProgress.textContent = error.message;
            }), 3000);
        }
    };

    mapsForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (pollTimer) window.clearTimeout(pollTimer);
        const form = new FormData(mapsForm);
        const payload = {
            query: String(form.get('query') ?? ''),
            purpose: String(form.get('purpose') ?? 'provider_discovery'),
            maximum_results: Number(form.get('maximum_results') ?? 20),
            category: String(form.get('category') ?? '') || null,
            radius_metres: Number(form.get('radius_metres') ?? 15000),
            open_now: form.get('open_now') === 'on',
        };
        if (mapsProgress) mapsProgress.textContent = 'Creating governed discovery search…';
        try {
            const body = await jsonRequest('/api/titan/maps-intelligence/searches', {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            const search = body?.data ?? body;
            await pollSearch(search.id);
        } catch (error) {
            if (mapsProgress) mapsProgress.textContent = error.message;
        }
    });
}
