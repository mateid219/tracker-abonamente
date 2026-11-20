/**
 * Small fetch() wrapper for JSON requests to Laravel.
 * Sends the CSRF token and the WebSocket socket id (X-Socket-ID), so the window that
 * makes a change does not receive its own broadcast back; the other windows do.
 */
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export class HttpError extends Error {
    constructor(status, data) {
        super(HttpError.messageFor(status, data));
        this.status = status;
        this.data = data;
    }

    static messageFor(status, data) {
        if (status === 422) return data?.message ?? 'Datele introduse nu sunt valide.';
        if (status === 419) return 'Sesiunea a expirat. Reîncarcă pagina și încearcă din nou.';
        if (status === 403) return 'Nu ai permisiunea pentru această acțiune.';
        if (status === 404) return 'Elementul nu mai există. Probabil a fost șters din altă fereastră.';
        return 'A apărut o eroare. Încearcă din nou.';
    }

    /** Validation errors as { field: 'first message' }. */
    get fieldErrors() {
        const errors = this.data?.errors ?? {};
        return Object.fromEntries(Object.entries(errors).map(([field, messages]) => [field, messages[0]]));
    }
}

export async function api(url, { method = 'GET', body } = {}) {
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
    };

    const socketId = window.Echo?.socketId?.();
    if (socketId) headers['X-Socket-ID'] = socketId;

    if (body !== undefined) headers['Content-Type'] = 'application/json';

    let response;
    try {
        response = await fetch(url, {
            method,
            headers,
            credentials: 'same-origin',
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });
    } catch {
        throw new HttpError(0, { message: 'Nu există conexiune la server.' });
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) throw new HttpError(response.status, data);

    return data;
}
