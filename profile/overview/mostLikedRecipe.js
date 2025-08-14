import {LikeClicker} from "../../likes/likeClicker.js";

document.addEventListener('DOMContentLoaded',()=>{
   const topRecipe = document.querySelector('.top-liked-recipe');
   if(topRecipe){
       LikeClicker(topRecipe);
   topRecipe.addEventListener('click', async(e) =>{
       const {target} = e;
       if(target.classList.contains('delete-icon')){
           const id = target.dataset.id;
           if(confirm("Are you sure you want to delete this recipe?")){
               const res = await fetch(`/profile/posts/delete.php?id=${id}`, {
                   method: 'POST',
                   credentials: 'include',
                   headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                   body: new URLSearchParams({ id }),
               });
               const result = await res.json();
               if(result.status ==='success') {
                   topRecipe.remove();
               }else{
                   alert('Failed to delete recipe')
               }
           }
       }
   });
   }
});
