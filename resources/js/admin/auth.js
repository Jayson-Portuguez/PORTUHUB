export const ADMIN_TOKEN_KEY = 'admin_session_token';

export function authHeaders() {
  const headers = {};
  const token = sessionStorage.getItem(ADMIN_TOKEN_KEY);
  if (token) headers.Authorization = `Bearer ${token}`;
  return headers;
}
