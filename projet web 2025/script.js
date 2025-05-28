const game=document.querySelectorAll(".card");
console.log(game);
let cardOne ,cardTwo;
let matcheGame =0;

function flipCard(e)
{
    /*utilisateur clique sur card */
let clickedCard =e.target;

if (clickedCard !== cardOne )
{
/*donner class card flip */
clickedCard.classList.add("flip");
    if(!cardOne)
    {// return premier card lorsque click
        return cardOne=clickedCard;

    }
    cardTwo=clickedCard;

    let cardOneImg =cardOne.querySelector("img").src,
    cardTwoImg = cardTwo.querySelector("img").src;
    matchGame(cardOneImg,cardTwoImg);
}

}

function matchGame(img1,img2){
   if (img1 === img2)
   { matcheGame++;
    if(matcheGame == 8)
    {
        //apres 1s faire evente de ?
      setTimeout(()=>{
        return suffleCard();
      },1000)
    }
  cardOne.removeEventListener("click",flipCard);
   cardTwo.removeEventListener("click",flipCard);
   cardOne =cardTwo ="";
return;
}

/*si img1!=img2 ajouter apres 400ms*/
   setTimeout(() => {
    cardOne.classList.add("shake");
    cardTwo.classList.add("shake");
    },
    400);

     setTimeout(() => {
        //supprimer apres 1.2 seconds
        cardOne.classList.remove("shake","flip");
        cardTwo.classList.remove("shake","flip");
        cardOne = cardTwo ="";
    },
        1200);
}

function suffleCard(){
    matcheGame =0;
    cardOne = cardTwo = "";

     /*pour aleo de jeu*/
    let arr=[1,2,3,4,5,6,7,8,1,2,3,4,5,6,7,8]
    arr.sort(()=> Math.random() > 0.5 ? 1 : -1);

    game.forEach((card,i) =>{
        card.classList.remove("flip");
        
        let imgTag= card.querySelector("img");
        imgTag.src=`images/img${arr[i]}.jpeg`;
        /*lorsque clique ajouter event pour tous game */
        card.addEventListener("click",flipCard);
    });
  
}

suffleCard()
game.forEach(card =>{
    /*commancer par image*/
    /*card.classList.add("flip");*/
    /*lorsque clique ajouter event pour tous game */
    card.addEventListener("click",flipCard);
});