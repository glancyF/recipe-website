import {LikeClicker} from "../../likes/likeClicker.js";

document.addEventListener('DOMContentLoaded',()=>{
   const topRecipe = document.querySelector('.top-liked-recipe');
   if(topRecipe){
       LikeClicker(topRecipe);
   }

});