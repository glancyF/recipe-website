import {renderPagination, escapeHtml} from "../../utils/recipeHelper.js";

document.addEventListener('DOMContentLoaded',()=>{
   const tbody=document.getElementById('usersTbody');
   const pager = document.getElementById('usersPagination');
   let currentPage =1;
   const pageSize = 10;

   const load = async (page) =>{
       currentPage= page;
       const res = await fetch(`/profile/admin/list.php?page=${page}&limit=${pageSize}`, {credentials: 'include'});
        let data = null;
        try { data = await res.json();} catch {}
       if(!data || data.status !== 'success'){
           tbody.innerHTML = `<tr><td colspan="4">Failed to load</td></tr>`;
           pager.innerHTML='';
           return;
       }
       renderRows(data.users || []);
       renderPagination(pager,data.total,data.page,data.limit,load);
   };
    function renderRows(users){
        tbody.innerHTML ='';
        if(!users.length) {
            tbody.innerHTML =`<tr><td colspan="4">No users</td></tr>`;
        }
        users.forEach(u => {
            const isMe = Number(window.currentUserId) === Number(u.id);
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td>${u.id}</td>
        <td>
          <div><strong>${escapeHtml(u.username ?? '')}</strong></div>
          <div class="muted">${escapeHtml(u.email ?? '')}</div>
        </td>
        <td>
          <select class="status-select" data-id="${u.id}" ${isMe ? 'disabled' : ''}>
            <option value="user"  ${u.status === 'user'  ? 'selected' : ''}>user</option>
            <option value="admin" ${u.status === 'admin' ? 'selected' : ''}>admin</option>
          </select>
        </td>
        <td>
          <button class="apply-btn" data-id="${u.id}" ${isMe ? 'disabled' : ''}>Apply</button>
        </td>
      `;
            tbody.appendChild(tr);
        });
    }
    tbody.addEventListener('click', async(e) =>{
        if(!e.target.classList.contains('apply-btn')) return;
        const id = e.target.dataset.id;
        const sel = tbody.querySelector(`.status-select[data-id="${id}"]`);
        if(!sel) return;

        const form = new FormData();
        form.append('user_id',id);
        form.append('status',sel.value);
        form.append('csrf',window.csrfToken || '');

        const res = await fetch('/profile/admin/update_status.php',{
           method: 'POST',
           credentials: 'include',
            body: form
        });
        const data = await res.json();
        if(data.status !== 'success'){
            alert(data.message || 'Failed to update');
            return;
        }
        e.target.textContent='Saved';
        setTimeout(()=> (e.target.textContent ='Apply'),800);
    });
    load(1);
});

