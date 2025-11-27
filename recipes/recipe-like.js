import {LikeClicker} from "../utils/common.js";

document.addEventListener('DOMContentLoaded',()=>{
   const container = document.querySelector('.recipe-page');
   if(container){
       LikeClicker(container);
   }
});