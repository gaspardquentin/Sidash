var fond = document.createElement('audio');
fond.src = "musique/sidash.mp3";
fond.volume = 0.4;
fond.loop = true;
fond.play();

window.addEventListener ('load', function(){
    const canvas = document. getElementById('gameCanvas');
    const ctx = canvas.getContext ('2d') ;
    canvas.width = window.innerWidth;
    canvas.height = this.window.innerHeight;
    // canvas.height = window.screen.height;
    
    
    let enemies = [];
    let projectiles = [];
    let recharges = [];
    let lePopup = [];
    let munitions = [new Image(10, 10), new Image(10, 10), new Image(10, 10), new Image(10, 10), new Image(10, 10), new Image(10, 10), new Image(10, 10), new Image(10, 10) ,new Image(10, 10), new Image(10, 10)];
    let gameOver = false;
    let pauseGame = false;
    let score = 69;
    let playerX = 0;
    let playerY = 0;
    let nbProjectiles = 10;
    let deltaTime = 50;
    let textPopup = ["Le sida peut se transmettre au fœtus si la mère est porteuse du sida.", 
    "Le sida peut être également transmis par le sang lors d'un échange de seringue ou d’une plaie infectée.", 
    "Une personne séropositive peut prendre un traitement pour éviter de transmettre le sida.", 
    "Les préservatifs masculins et féminins protègent presque totalement.", 
    "Le sida touche 4000 personnes par jour, soit 3 personnes par minute.", 
    "Il existe un comprimé préventif pour les personnes séronégatives.", 
    "En 2021, 650 000 personnes sont décédés du VIH dans le monde.", 
    "Protégez-vous à chaque rapport où conséquence !", 
    "Si vous vous sentez potentiellement menacé par le VIH, dépistez-vous", 
    "Le dépistage est très conseillé avant une relation sexuelle et après une prise de risque afin de détecter une potentielle infection sexuellement transmissible.", 
    "Vous pouvez vous faire dépister anonymement.", 
    "Pour savoir si on est séropositif, un autotest peut être réalisé", 
    "Guérir du sida est impossible mais un traitement existe pour étendre son éspérence de vie.", 
    "Les symptômes du sida sont le plus souvent la fièvre, les maux de têtes, les vomissements", 
    "Les personnes les plus touchées par le VIH sont les hommes.", 
    "? Le VIH signifie Le virus de l'immunodéficience humaine"
    ]
    
    class InputHandler {
        constructor (){
            this.keys = [];
            window.addEventListener ('keydown' , e => {
                if (( e.key === 'ArrowDown' ||
                    e.key === 'ArrowUp')
                    && this.keys.indexOf(e.key) === -1){
                this.keys.push(e.key);
                }
                if (e.key === 'Enter' && !gameOver && pauseGame) {
                    removePopup()
                }
                if (e.key === ' ' && !pauseGame && nbProjectiles > 0) {
                    let piou = document.createElement('audio');
                    piou.src = "musique/piou.mp3"
                    piou.volume = 0.3
                    piou.play()
                    shoot();
                    
                }
            });
            window.addEventListener('keyup', e => {
            if (e.key === 'ArrowDown' ||
                e.key === 'ArrowUp'){
            this.keys.splice(this.keys.indexOf(e.key), 1);
            }

        });
        }
    }
    class Player {
        constructor (gameWidth, gameHeight){
            this.gameWidth = gameWidth;
            this.gameHeight = gameHeight;
            this.width = 140;
            this.height = 30;
            this.y = this.gameHeight - 120;
            this.x = 10;
            this.image = document.getElementById("playerImage1");
            this.vy = 0;
        }

        draw (context){

            // context.strokeStyle = 'white';
            // context.strokeRect(this.x, this.y, this.width, this.height)
            // context.beginPath();
            // context.arc(this.x + this.width/2, this.y + this.height/2, this.width/2, 0, Math.PI * 2);
            // context.stroke();
            this.image = playerImage();
            context.drawImage(this.image, this.x, this.y, this.width, this.height);
        }

        update (input, enemies) {
            // collision detection
            enemies.forEach(enemy => {
                const dx = enemy.x - this.x;
                const dy = enemy.y - this.y;
                const distance = Math.sqrt(dx * dx + dy * dy) * 2;
                if ( distance < enemy.width/2 + this.width/2) {
                    gameOver = true;
                }
            });
            recharges.forEach(recharge => {
            const dx = recharge.x - this.x;
            const dy = recharge.y - this.y;
            const distance = Math.sqrt(dx * dx + dy * dy) * 2;
            if ( distance < projectile.width/2 + this.width/2) {
                delete recharges[recharges.indexOf(recharge)];
                for (let i = 0; i < 5; i++) {
                    munitions.push(new Image(10, 10))
                    nbProjectiles += 1;
                }
                }
            });
            if (input.keys.indexOf('ArrowUp') > -1 && this.y > 120) {
                this.vy -= 10;
            } else if(input.keys.indexOf('ArrowDown') > -1 && this.y < this.gameHeight - 120) {
                this.vy += 10
            }
            // vertical movement 
            this.y += this.vy;
            
            this.vy = 0;
            playerX = this.x;
            playerY = this.y;
        }
    }

    let imageTimer = 0;
    function playerImage() {
        imageTimer += 50;
        if (imageTimer == 1000) {
            imageTimer = 0;
        }
        if(imageTimer > 500) {
            return document.getElementById("playerImage1");
        }
        return document.getElementById("playerImage2");
    }

    class Background {
            constructor (gameWidth, gameHeight){
                this.gameWidth = gameWidth;
                this.gameHeight = gameHeight;
                this.image = document.getElementById('backgroundImage');
                this.x = 0;
                this.y = 0;
                this.width = 2400;
                this.height = window.innerHeight;
                this.speed = 7;
            }
            draw(context){
                context.drawImage(this.image, this.x, this.y, this.width, this.height);
                context.drawImage(this.image, this.x + this.width - this.speed, this.y, this.width, this.height);
            }
            update(){
                this.x -= this.speed;
                if (this.x < 0 - this.width) this.x = 0;
            }
    }
    class Enemy {
        constructor(gameWidth, gameHeight){
            this.gameWidth = gameWidth;
            this.gameHeight = gameHeight;
            this.width = 160;
            this.height = 120;
            this.x = this.gameWidth;
            this.y = Math.random() * (this.gameHeight - 200) + this.height / 2;
            this.image = document.getElementById("enemyImage");
            this.speed = 8;
            this.fps = 20;
        }
        draw(context){
            // context.strokeStyle = 'white';
            // context.strokeRect(this.x, this.y, this.width, this.height)
            // context.beginPath();
            // context.arc(this.x + this.width/2, this.y + this.height/2, this.width/2, 0, Math.PI * 2);
            // context.stroke();
            context.drawImage(this.image, this.x, this.y, this.width, this.height);
        }
        update(enemies, recharges){ 
            projectiles.forEach(projectile => {
            const dx = projectile.x - this.x;
            const dy = projectile.y - this.y;
            const distance = Math.sqrt(dx * dx + dy * dy) * 2;
            if ( distance < projectile.width/2 + this.width/2) {
                let piou = document.createElement('audio');
                piou.src = "musique/mortVirus.mp3"
                piou.volume = 0.3
                piou.play()
                delete enemies[enemies.indexOf(this)];
                projectiles.shift();
                score += 1;
                }
            });
            this.x -= this.speed;
        }
    }

    function handleEnemies (deltaTime){
        if (enemyTimer > enemyInterval + randomEnemyInterval) {
            enemies.push(new Enemy(canvas.width, canvas.height));
            enemyTimer = 0;
        } else {
            enemyTimer += deltaTime;
        }
        enemies.forEach(enemy => {
            if(Math.random() * 10 > 8) {
                enemy.y += randomMove()
            }
            enemy.draw(ctx);
            enemy.update(enemies);
            enemy.speed += 0.5;
        })
    }

    function randomMove() {
        return Math.random() * (50) - 25;
    }
    class Projectile {
        constructor(gameWidth, gameHeight) {
            this.gameWidth = gameWidth;
            this.gameHeight = gameHeight;
            this.width = 155;
            this.height = 42;
            this.x = playerX / 2;
            this.y = playerY;
            this.image = document.getElementById("projectile")
            this.speed = 10;
        }
        draw(context){
            // context.strokeStyle = 'white';
            // context.strokeRect(this.x, this.y, this.width, this.height)
            // context.beginPath();
            // context.arc(this.x + this.width/2, this.y + this.height/2, this.width/2, 0, Math.PI * 2);
            // context.stroke();
            context.drawImage(this.image, this.x, this.y, this.width, this.height);
        }
        update(){
            this.x += this.speed;
        }
    }
    
    function handleProjectiles () {
        projectiles.forEach(projectile => {
            projectile.draw(ctx);
            projectile.update();
        })
    }
  
    class Recharge {
        constructor(gameWidth, gameHeight) {
            this.gameWidth = gameWidth;
            this.gameHeight = gameHeight;
            this.width = 92;
            this.height = 151;
            this.x = gameWidth - 100;
            this.y = Math.random() * (this.gameHeight - 200) + this.height / 2;
            this.image = document.getElementById("recharge");
            this.speed = 10;
        }
        draw(context){
            // context.strokeStyle = 'white';
            // context.strokeRect(this.x, this.y, this.width, this.height)
            // context.beginPath();
            // context.arc(this.x + this.width/2, this.y + this.height/2, this.width/2, 0, Math.PI * 2);
            // context.stroke();
            context.drawImage(this.image, this.x, this.y, this.width, this.height);
        }
        update(){
            this.x -= this.speed;
        }
    }

    let rechargeTimer = 0;
    let rechargeInterval = 40000;
    let randomRechargeinterval = Math.random() * 20000 + 500;

    function handleRecharge(deltaTime){
        if (rechargeTimer > rechargeInterval + randomRechargeinterval) {
            recharges.push(new Recharge(canvas.width, canvas.height));
            rechargeTimer = 0;
        } else {
            rechargeTimer += deltaTime;
        }
        recharges.forEach(recharge => {
            recharge.draw(ctx);
            recharge.update();
        })
    }

    function displayStatusText (context){
        context.font = '40px Helvetica';
        context.fillStyle = 'balck';
        context.fillText('Score: ' + score, 20, 50)
        if (gameOver) {
            context.textAlign = 'center';
            context.fillStyle = 'black';
            context.fillText('OUPS... Vous avez choppé le VIH !!!', canvas.width / 2, 200)
        }
        drawMunitions(context)
    }

    function drawMunitions (context){
        let i = 0;
        munitions.forEach(munition => {
            munition.src = "imgs/capote.png"
            context.drawImage(munition, 5 + i * 35, canvas.height - 60, 30, 30)
            i++
        })
    }

    const input = new InputHandler();
    const player = new Player(canvas.width, canvas.height);
    const background = new Background(canvas.width, canvas.height);

    let enemyTimer = 0;
    let enemyInterval = 2000;
    let randomEnemyInterval = Math.random() * 1000 + 500;

    function animate(){
        // let deltaTime = 50;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        background.draw(ctx);
        background.update();
        player.draw(ctx);
        player.update(input, enemies, recharges);
        handleEnemies(deltaTime);
        handleRecharge(deltaTime)
        handleProjectiles();
        displayStatusText(ctx);
        if ((score) % 10 == 0) {
            showPopup()
        }
        if (!gameOver && !pauseGame) {
            requestAnimationFrame(animate);
        } else if(score == 69){
            
            shomImages()
        }
        deltaTime += 0.01;
    }

    function shoot(){
        projectiles.push(new Projectile(canvas.width, canvas.height))
        nbProjectiles -= 1;
        munitions.pop();
    }

    function showPopup() {
        let splash = document.createElement('audio');
        splash.src = "musique/splash.mp3"
        splash.volume = 1
        splash.play()
        document.getElementById("popup").style.zIndex= 0;
        document.getElementById("popup").style.position = "absolute";
        document.getElementById("popup").classList.add("pop");
        document.getElementById("popup").style.width = canvas.width + "px"
        document.getElementById("popup").style.height = canvas.height + "px"
        
        document.getElementById("popupText").style.position = "absolute";
        document.getElementById("popupText").style.zIndex = 0;
        document.getElementById("popupText").style.width = canvas.width / 2 + "px"
        document.getElementById("popupText").style.left = canvas.width / 2 - document.getElementById("popupText").offsetWidth / 2 + "px"
        document.getElementById("popupText").style.top = canvas.height / 2 - document.getElementById("popupText").offsetHeight / 2  - 50     + "px"
        document.getElementById("popupText").style.textAlign = "center";
        document.getElementById("popupText").style.fontSize = "2em";
        document.getElementById("popupText").style.fontFamily = "arial,sans-serif";

        document.getElementById("popupText").innerText = textPopup[Math.floor(Math.random() * textPopup.length)]
        document.getElementById("popupText").classList.add("pop");
        pauseGame = true;
    }

    function removePopup() {
        document.getElementById("popup").style.zIndex= -1;
        document.getElementById("popup").classList.remove("pop");
        document.getElementById("popupText").style.zIndex = -1;
        document.getElementById("popupText").classList.remove("pop");
        score += 1;
        pauseGame = false;
        animate();
    }

    function shomImages() {
        for(let i = 0; i < 12; i++) {
            console.log(i)
            setTimeout(() => { initImage(i); }, i * 1000);
        }  
    }

    function initImage(i){
        var image = document.getElementById("p" + i)
        image.style.top = 0 + "px";
        image.style.left = 0 + "px";
        image.classList.add("pop")
        image.style.zIndex = 1;
        image.style.visibility = 'visible';
        image.style.width = canvas.width + "px"
        image.style.height = canvas.height + "px"
    }
    animate();
});