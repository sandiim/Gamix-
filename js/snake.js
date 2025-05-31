const canvas = document.getElementById('gameCanvas');
const ctx = canvas.getContext('2d');
const startButton = document.getElementById('start-btn');
const scoreElement = document.getElementById('score');
const bestScoreElement = document.getElementById('bestScore');

const gridSize = 20;
const tileCount = 20;

let snake = [];
let food = {};
let velocityX = 0;
let velocityY = 0;
let score = 0;
let gameRunning = false;
const speed = 7; // moves per second
const fps = 120;  // animation frames per second
let lastMoveTime = 0;
let bestScore = localStorage.getItem('bestScore') || 0;
let lastDirection = { x: 0, y: 0 };

const eatSound = new Audio('eat.mp3');
const gameOverSound = new Audio('gameover.mp3');

// To store pixel position for smooth animation
let pixelSnake = [];

function updateBestScore() {
    if (score > bestScore) {
        bestScore = score;
        localStorage.setItem('bestScore', bestScore);
    }
    bestScoreElement.textContent = bestScore;
}

function initGame() {
    snake = [{ x: 10, y: 10 }];
    pixelSnake = [{ x: 10 * gridSize, y: 10 * gridSize }];
    placeFood();
    score = 0;
    scoreElement.textContent = score;
    velocityX = 0;
    velocityY = 0;
    lastDirection = { x: 0, y: 0 };
    gameRunning = true;
    lastMoveTime = 0;
    requestAnimationFrame(gameLoop);
}

function placeFood() {
    food = {
        x: Math.floor(Math.random() * tileCount),
        y: Math.floor(Math.random() * tileCount),
        animationFrame: 0,
    };
}

function drawGrid() {
    ctx.strokeStyle = 'rgba(100, 100, 100, 0.1)';
    for (let i = 0; i < tileCount; i++) {
        ctx.beginPath();
        ctx.moveTo(i * gridSize, 0);
        ctx.lineTo(i * gridSize, canvas.height);
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(0, i * gridSize);
        ctx.lineTo(canvas.width, i * gridSize);
        ctx.stroke();
    }
}

function drawFood() {
    const pulse = Math.sin(food.animationFrame / 30) * 0.5 + 0.5;
    ctx.shadowBlur = 20 * pulse;
    ctx.shadowColor = '#f1c40f';
    ctx.fillStyle = `rgba(243, 156, 18, ${0.7 + 0.3 * pulse})`;
    ctx.beginPath();
    ctx.arc(food.x * gridSize + gridSize / 2, food.y * gridSize + gridSize / 2, gridSize / 2, 0, Math.PI * 2);
    ctx.fill();
    ctx.shadowBlur = 0;

    food.animationFrame++;
}

function drawSnake() {
    snake.forEach((segment, index) => {
        const px = pixelSnake[index].x;
        const py = pixelSnake[index].y;
        const gradient = ctx.createLinearGradient(px, py, px + gridSize, py + gridSize);
        if (index === 0) {
            gradient.addColorStop(0, '#1abc9c');
            gradient.addColorStop(1, '#16a085');
        } else {
            gradient.addColorStop(0, '#2ecc71');
            gradient.addColorStop(1, '#27ae60');
        }
        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.arc(px + gridSize / 2, py + gridSize / 2, gridSize / 2, 0, Math.PI * 2);
        ctx.fill();
    });
}

function update(time) {
    if (!gameRunning) return;

    // Move snake on grid according to speed
    if (!lastMoveTime) lastMoveTime = time;
    const elapsed = time - lastMoveTime;
    const moveInterval = 1000 / speed;

    if (elapsed > moveInterval) {
        lastMoveTime = time - (elapsed % moveInterval);

        let newHead = {
            x: snake[0].x + velocityX,
            y: snake[0].y + velocityY
        };

        if (velocityX !== 0 || velocityY !== 0) {
            if (
                newHead.x < 0 || newHead.x >= tileCount ||
                newHead.y < 0 || newHead.y >= tileCount ||
                snake.some(seg => seg.x === newHead.x && seg.y === newHead.y)
            ) {
                gameOver();
                return;
            }
        }

        snake.unshift(newHead);

        // Insert corresponding pixel position at front for animation
        pixelSnake.unshift({ x: newHead.x * gridSize, y: newHead.y * gridSize });

        if (newHead.x === food.x && newHead.y === food.y) {
            score += 10;
            scoreElement.textContent = score;
            eatSound.play();
            placeFood();
        } else {
            snake.pop();
            pixelSnake.pop();
        }

        lastDirection = { x: velocityX, y: velocityY };
    }

    // Smooth animation interpolation of pixel positions between grid cells
    // Calculate progress between lastMoveTime and next move
    const progress = Math.min(1, elapsed / moveInterval);

    for (let i = 1; i < pixelSnake.length; i++) {
        // Previous segment logical position
        const prevSeg = snake[i];
        const nextSeg = snake[i - 1];

        // Calculate start and end pixel positions
        const startX = prevSeg.x * gridSize;
        const startY = prevSeg.y * gridSize;
        const endX = nextSeg.x * gridSize;
        const endY = nextSeg.y * gridSize;

        // Linear interpolation for smooth movement
        pixelSnake[i].x = startX + (endX - startX) * progress;
        pixelSnake[i].y = startY + (endY - startY) * progress;
    }
}

function draw() {
    ctx.fillStyle = '#171717';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    drawGrid();
    drawSnake();
    drawFood();
}

function gameLoop(time = 0) {
    if (!gameRunning) return;

    update(time);
    draw();

    requestAnimationFrame(gameLoop);
}

function gameOver() {
    gameRunning = false;
    updateBestScore();
    gameOverSound.play();
    alert(`Game Over! Votre score: ${score}`);
}

window.addEventListener('keydown', e => {
    if (!gameRunning) return;

    // Prevent snake from reversing direction directly
    switch (e.key) {
        case 'ArrowUp':
            if (lastDirection.y === 1) break;
            velocityX = 0; velocityY = -1;
            break;
        case 'ArrowDown':
            if (lastDirection.y === -1) break;
            velocityX = 0; velocityY = 1;
            break;
        case 'ArrowLeft':
            if (lastDirection.x === 1) break;
            velocityX = -1; velocityY = 0;
            break;
        case 'ArrowRight':
            if (lastDirection.x === -1) break;
            velocityX = 1; velocityY = 0;
            break;
    }
});

startButton.addEventListener('click', () => {
    if (!gameRunning) {
        initGame();
    }
});
