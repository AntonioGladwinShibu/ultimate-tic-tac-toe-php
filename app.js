document.addEventListener('DOMContentLoaded', () => {
    const boardEl = document.getElementById('board');
    const status = document.getElementById('status');
    const scoreX = document.getElementById('scoreX');
    const scoreO = document.getElementById('scoreO');
    const scoreD = document.getElementById('scoreD');

    let board = ['', '', '', '', '', '', '', '', ''];
    let current = 'X';
    let moves = 0;

    function makeBoard() {
        boardEl.innerHTML = '';
        for (let i = 0; i < 9; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cell';
            btn.dataset.index = i;
            btn.addEventListener('click', () => cellClick(i));
            boardEl.appendChild(btn);
        }
        render();
    }

    function render() {
        for (let i = 0; i < 9; i++) {
            const cell = boardEl.children[i];
            cell.textContent = board[i];
            cell.classList.remove('X', 'O');
            if (board[i]) {
                cell.classList.add(board[i]);
            }
        }
        const winner = checkWinner();
        if (winner) {
            if (winner === 'Draw') {
                status.textContent = "It's a draw!";
            } else {
                status.textContent = `${window.TTT.players[winner]} (${winner}) wins!`;
            }
        } else {
            status.textContent = `${window.TTT.players[current]}'s Turn (${current})`;
        }

        scoreX.textContent = window.TTT.scores.X;
        scoreO.textContent = window.TTT.scores.O;
        scoreD.textContent = window.TTT.scores.D;
    }

    function cellClick(i) {
        if (board[i] || checkWinner()) return;
        board[i] = current;
        moves++;
        render();
        const result = checkWinner();
        if (result) {
            setTimeout(() => {
                if (result === 'Draw') {
                    postResult('Draw');
                } else {
                    postResult(current);
                }
            }, 300);
            return;
        }
        current = current === 'X' ? 'O' : 'X';
        render();
    }

    function checkWinner() {
        const wins = [
            [0, 1, 2],
            [3, 4, 5],
            [6, 7, 8],
            [0, 3, 6],
            [1, 4, 7],
            [2, 5, 8],
            [0, 4, 8],
            [2, 4, 6]
        ];
        for (const [a, b, c] of wins) {
            if (board[a] && board[a] === board[b] && board[b] === board[c]) {
                return board[a];
            }
        }
        if (board.every(cell => cell !== '')) return 'Draw';
        return null;
    }

    function postResult(result) {
        const fd = new FormData();
        fd.append('result', result);
        fetch('index.php', { method: 'POST', body: fd })
            .then(() => {
                if (result !== 'Draw') {
                    launchConfetti();
                }
                setTimeout(() => location.reload(), 1000);
            });
    }

    function launchConfetti() {
        const canvas = document.createElement('canvas');
        canvas.className = 'confetti-canvas';
        document.body.appendChild(canvas);
        canvas.width = innerWidth;
        canvas.height = innerHeight;
        const ctx = canvas.getContext('2d');
        const pieces = [];
        for (let i = 0; i < 90; i++) {
            pieces.push({
                x: Math.random() * canvas.width,
                y: Math.random() * -canvas.height,
                r: Math.random() * 6 + 4,
                dx: Math.random() * 4 - 2,
                dy: Math.random() * 4 + 2,
                color: `hsl(${Math.random() * 360}, 80%, 60%)`
            });
        }
        let frame = 0;
        function step() {
            frame++;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            pieces.forEach(p => {
                p.x += p.dx;
                p.y += p.dy;
                p.dy += 0.05;
                ctx.fillStyle = p.color;
                ctx.beginPath();
                ctx.ellipse(p.x, p.y, p.r, p.r * 0.6, 0, 0, Math.PI * 2);
                ctx.fill();
            });
            if (frame < 140) requestAnimationFrame(step);
            else canvas.remove();
        }
        requestAnimationFrame(step);
    }

    makeBoard();

    document.addEventListener('keydown', e => {
        if (e.key >= '1' && e.key <= '9') {
            const idx = parseInt(e.key, 10) - 1;
            const btn = boardEl.children[idx];
            if (btn && !btn.disabled) btn.click();
        }
    });

    if (window.TTT && window.TTT.lastWinner) {
        const history = JSON.parse(localStorage.getItem('ttt_history') || '[]');
        history.unshift({
            date: new Date().toISOString(),
            playerX: window.TTT.players.X,
            playerO: window.TTT.players.O,
            winner: window.TTT.lastWinner
        });
        localStorage.setItem('ttt_history', JSON.stringify(history.slice(0, 50)));
    }
});
