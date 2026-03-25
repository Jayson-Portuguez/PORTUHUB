const s="admin_session_token";function n(){const e={},t=sessionStorage.getItem(s);return t&&(e.Authorization=`Bearer ${t}`),e}export{s as A,n as a};
