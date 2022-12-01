window.addEventListener ('load', function(){
    const canvas = document. getElementById('gameCanvas');
    const ctx = canvas.getContext ('2d') ;
    canvas.width = window.innerWidth;
    canvas. height = window.innerHeight;
    let enemies = [];
    
    class InputHandler {
        constructor (){
            this.keys = [];
            window.addEventListener ('keydown' , e => {
                if (( e.key === 'ArrowDown' ||
                    e.key === 'ArrowUp' ||
                    e.key === 'ArrowLeft' ||
                    e.key === 'ArrowRight')
                    && this.keys.indexOf(e.key) === -1){
                this.keys.push(e.key);
                }
            });
            window.addEventListener('keyup', e => {
            if (
                e.key === 'ArrowDown' ||
                e.key === 'ArrowUp' ||
                e.key === 'ArrowLeft' ||
                e.key ==='ArrowRight'){
            this.keys.splice(this.keys.indexOf(e.key), 1);
            }
        });
        }
    }
    class Player {
        constructor (gameWidth, gameHeight){
            this.gameWidth = gameWidth;
            this.gameHeight = gameHeight;
            this.width = 200;
            this.height = 200;
            this.x = 10;
            this.y = this.gameHeight - this. height;
            this.image = document.getElementById("playerImage");
            this.frameX = 0;
            this.frameY = 0;
            this.speed = 10;
            this.vy = 0;
            this.weigth = 0;
            this.fps = 20;
            this.frameTimer = 0;
            this.frameInterval = 1000 / this.fps;
        }
        draw (context){
            //context.fillStyle = 'white';
            //context.fillRect(this.x, this.y, this.width, this.height);
            context.drawImage(this.image, this.x, this.y, this.width, this.height);
        }
        update (input) {
            if (this.frameX >= this.maxFrame) this.frameX = 0;
            else this.frameX++;
            if (input.keys.indexOf('ArrowRight') > -1){
                    this.speed = 5;
            } else if (input.keys. indexOf('ArrowLeft') > -1) {
                this.speed = -5;
            } else if (input.keys.indexOf('ArrowUp') > -1 && this.onGround()) {
                this.vy -= 32;
            } else {
                this.speed = 0;
            }
                // horizontal movement 
                this.x += this.speed;
                if (this.x < 0) this.x = 0;
                else if (this.x > this.gameWidth - this.width) this.x = this.gameWidth - this.width
                // vertical movement 
                this.y += this.vy;
                if (!this.onGround()){
                    this.vy += this.weight;
                    this.frameY = 1;
                } else {
                    this.vy = 0;
                    this.frameY = 0;
                }
                if (this.y > this.gameHeight - this.height) this.y = this.gameHeight - this.height
            
        }
        onGround(){
            return this.y >= (this.gameHeight - this.height);
        }
    }
    class Background {
            constructor (gameWidth, gameHeight){
                this.gameWidth = gameWidth;
                this.gameHeight = gameHeight;
                this.image = document.getElementById('backgroundImage');
                this.x = 0;
                this.y = 0;
                this.width = 2400;
                this.height = 720;
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
            this.height = 119;
            this.x = this.gameWidth - this.width;
            this.y = this.gameHeight - this.height;
            this.image = document.getElementById("enemyImage");
            this.speed = 8;
            this.maxFrame = 5;
            this.fps = 20;
            this.frameX = 0;
            this.frameTimer = 0;
            this.frameInterval = 1000 / this.fps;
        }
        draw(context){
            context.drawImage(this.image, this.x, this.y, this.width, this.height);
        }
        update(deltaTime){
            if (this.frameTimer > this.frameInterval){
                if (this.frameX < this.maxFrame) this.frameX = 0;
                else this.frameX++;
                this.frameTimer = 0;
            } else {
                this.frameTimer += deltaTime;
            }
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
            enemy.draw(ctx);
            enemy.update(deltaTime);
        })
    }
    function displayStatusText (){
    }
    const input = new InputHandler();
    const player = new Player(canvas.width, canvas.height);
    const background = new Background(canvas.width, canvas.height);

    let lastTime = 0;
    let enemyTimer = 0;
    let enemyInterval = 2000;
    let randomEnemyInterval = Math.random() * 1000 + 500;

    function animate(timeStamp){
        const deltaTime = timeStamp - lastTime;
        lastTime = timeStamp;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        background.draw(ctx);
        background.update();
        player.draw(ctx);
        player.update(input);
        handleEnemies(deltaTime);
        requestAnimationFrame(animate) ;
    }
    animate(0);
});