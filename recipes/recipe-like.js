import {LikeClicker} from "/likes/likeClicker.js";

document.addEventListener('DOMContentLoaded',()=>{
   const container = document.querySelector('.recipe-page');
   if(container){
       LikeClicker(container);
   }
});