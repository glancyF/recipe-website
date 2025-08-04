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
                    <a href="/profile/posts/edit.php?id=${recipe.id}" title="Edit">
                    <i class="fas fa-edit"></i></a>  
                    <i class="fas fa-trash delete-icon" data-id="${recipe.id}" title="Delete"></i>
                   
                </div>
                <div class="image-container">
                <img src="/uploads/${recipe.image_path}" alt="Image">
                <div class="like-container" data-id="${recipe.id}">
                <i class="fa fa-heart${recipe.liked ? ' liked' : ''}"></i>
                <span class="like-count">${recipe.like_count}</span>
                </div>
                </div>
                <h3>${escapeHtml(recipe.name)}</h3>
                <p>${escapeHtml(recipe.description)}</p>
                <span class="category ${recipe.category === 'lunch' ? 'highlight-category' : ''}">
                    ${escapeHtml(recipe.category)}
                </span>
                <p class="date">${new Date(recipe.created_at).toLocaleDateString()}</p>
                <div class="view"> <a href="/recipes/recipes.php?id=${recipe.id}">View</a></div>
                
                
            `;
           container.appendChild(card);


       });
   }
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

   function renderPagination(total,currentPage,limit){
       const totalPages = Math.ceil(total/limit);

       pagination.innerHTML='';

       for (let i =1;i <=totalPages; i++){
           const btn=document.createElement('button');
           btn.textContent = i;
           btn.disabled = i ===currentPage;
           btn.addEventListener('click',() =>{
              currentPage = i;
              fetchRecipes(currentPage)
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


    LikeClicker(container)

});

export function LikeClicker(container){
    container.addEventListener('click',async (e) =>{
        if (e.target.classList.contains('fa-heart')) {
            const likeIcon = e.target;
            const card = likeIcon.closest('.like-container');
            const recipeId = card.dataset.id;
            const res = await fetch(`/likes/toggle_like.php?id=${recipeId}`,{
                method: 'POST',
                credentials: 'include'
            });
            const result = await res.json();
            if(result.status ==='success') {
                likeIcon.classList.toggle('liked',result.liked);
                card.querySelector('.like-count').textContent = result.like_count;
            } else{
                alert('Failed to like the recipe');
            }
        }
    });
}
