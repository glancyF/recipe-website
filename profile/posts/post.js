document.addEventListener('DOMContentLoaded',()=>{
   const container = document.getElementById('recipesContainer');
   const pagination = document.getElementById('pagination');
   let currentPage = 1;

   async function fetchRecipes(page){
       const response = await fetch(`/profile/posts/post.php?page=${page}`,{
           credentials: 'include'
       });
       const data = await response.json();

       if(data.status !== 'success'){
           container.innerHTML = '<p>Failed to load recipes.</p>'
           return;
       }
       renderRecipes(data.recipes);
       renderPagination(data.total, data.page, data.limit);
   }
   function renderRecipes(recipes){
       container.innerHTML='';
       if(recipes.length ===0){
           container.innerHTML = '<p>No recipes found.</p>';
           return;
       }
       recipes.forEach(recipe =>{
           const card = document.createElement('div');
           card.classList.add('recipe-card');
           card.innerHTML = `
                <div class="card-header">
                    <i class="fas fa-trash delete-icon" data-id="${recipe.id}" title="Delete"></i>
                </div>
                <a href="/profile/posts/edit.php?id=${recipe.id}">
                    <i class="fas fa-edit"></i>
                </a>             
                <img src="/uploads/${recipe.image_path}" alt="Image">
                <h3>${recipe.name}</h3>
                <p>${recipe.description}</p>
                <span class="category">${recipe.category}</span>
                
                <p class="date">${new Date(recipe.created_at).toLocaleDateString()}</p>
                <div class="view"> <a href="../../recipes/recipes.php?id=${recipe.id}">View</a></div>
            `;
           container.appendChild(card);


       });
   }

   function renderPagination(total,currentPage,limit){
       const totalPages = Math.ceil(total/limit);

       pagination.innerHTML='';

       for (let i =1;i <=totalPages; i++){
           const btn=document.createElement('button');
           btn.textContent = i;
           btn.disabled = i ===currentPage;
           btn.addEventListener('click',() =>{
              fetchRecipes(i);
           });
           pagination.appendChild(btn);
       }
   }
   fetchRecipes(currentPage);


    container.addEventListener('click',async (e)=>{
        if(e.target.classList.contains('delete-icon')){
            const id=e.target.dataset.id;
            if(confirm('Are you sure you want to delete this recipe?')){
                const res = await fetch(`/profile/posts/delete.php?id=${id}`,{
                    method: 'DELETE',
                    credentials: 'include'
                });
                const result = await res.json()
                if(result.status ==='success'){
                    fetchRecipes(currentPage);

                }
                else{
                    alert('Failed to delete recipe');
                }
            }
        }
    });
});


