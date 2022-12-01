window.addEventListener ('load', function(){
    const canvas = document. getElementById('');
    const ctx = canvas.getContext ('2d') ;
    canvas.width = 800;
    canvas. height = 720;
    
    class InputHandler {
        constructor (){
            this.keys = [];
            window.addEventListener ('keydown' , e => {
                if (( e.key === 'ArrowDown' ||
                    e.key === 'ArrowUp' ||
                    e.key === 'ArrowLeft' ||
                    e.key === 'ArrowRight')
                    && this.keys.indexof(e.key) === -1){
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
            this.y = 10;
        }
        draw (context){
            context.fillStyle = 'white';
            context.fillRect(this.x, this.y, this.width, this .height);
        }
    }
    class Background {
    }
    class Enemy {
    }
    function handleEnemies (){
    }
    function displayStatusText (){
    }
    const input = new InputHandler();
    const player = new Player();
    player.draw(ctx);

    function animate(){
    }
});