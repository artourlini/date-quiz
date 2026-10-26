<?php
require __DIR__ . '/config.php';

page_head('Vee, will you go on a date with ' . ASKER_NAME . '?');
$photos = [
    ['photo1.jpg', 'best one by far'],
    ['photo4.jpg', '😛'],
    ['photo2.jpg', 'good morning 🥹'],
    ['photo6.jpg', 'before going home'],
    ['photo5.jpg', '😘'],
    ['photo3.jpg', 'palakihan ng noo'],
];
render_collage($photos);
?>
<main class="home">
  <p class="eyebrow">A very important question</p>
  <h1>Vee, will you go on a <em>date</em><br>with <?= h(ASKER_NAME) ?>?</h1>
  <p class="sub">Take your time. Choose carefully. There is definitely a right answer.</p>

  <form method="post" action="pick-a-time.php" class="buttons">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="answer" value="yes">
    <input type="hidden" name="no_count" id="noCount" value="0">
    <button type="submit" class="btn btn-yes" id="yes">Yes 💕</button>
    <button type="button" class="btn btn-no" id="no">No 😢</button>
  </form>

  <p class="plea" id="plea" aria-live="polite"></p>
</main>

<script>
  const yes = document.getElementById('yes');
  const no = document.getElementById('no');
  const plea = document.getElementById('plea');
  const noCount = document.getElementById('noCount');
  const lines = [
    'Are you sure?', 'Sure ka?', 'Tlga ba?', 'Last chance!',
    'Surely not?', 'Bb dont', 'Pagisipan mo ng mabuti.',
    'Sure na sure ka???', 'This could be a mistake!', 'Have a heart!',
    "Don't be so cold!", 'Say yes at pabubunitin ko sayo lahat ng pubes ko', 
    "Reconsider mo naman pls", 'Is that your final answer?', "You're breaking my heart ;(",
  ];
  let count = 0;

  function dodge() {
    count++;
    noCount.value = count;
    plea.textContent = lines[(count - 1) % lines.length];
    // Yes grows, No shrinks a little
    yes.style.transform = `scale(${Math.min(1 + count * 0.18, 4)})`;
    no.style.transform = `scale(${Math.max(1 - count * 0.05, 0.55)})`;
    // After a couple of tries, No starts running away
    if (count >= 2) {
      if (!no.classList.contains('dodging')) {
        // Move it to the top layer so nothing can cover it
        document.body.appendChild(no);
        no.classList.add('dodging');
      }
      const spot = findSpot();
      no.style.left = spot.x + 'px';
      no.style.top = spot.y + 'px';
    }
  }

  // Things the No button shouldn't land on: the photos and the Yes button
  function obstacles() {
    const gap = 16;
    return [...document.querySelectorAll('.polaroid'), yes]
      .map(el => el.getBoundingClientRect())
      .filter(r => r.width > 0 && r.bottom > 0 && r.top < innerHeight)
      .map(r => ({ l: r.left - gap, t: r.top - gap, r: r.right + gap, b: r.bottom + gap }));
  }

  function overlapArea(x, y, w, h, o) {
    const ox = Math.min(x + w, o.r) - Math.max(x, o.l);
    const oy = Math.min(y + h, o.b) - Math.max(y, o.t);
    return ox > 0 && oy > 0 ? ox * oy : 0;
  }

  // Try random spots until one is clear; otherwise use the least-covered one
  function findSpot() {
    const pad = 20;
    const w = no.offsetWidth, h = no.offsetHeight;
    const maxX = Math.max(pad, innerWidth - w - pad);
    const maxY = Math.max(pad, innerHeight - h - pad);
    const obs = obstacles();
    let best = null;
    for (let i = 0; i < 200; i++) {
      const x = pad + Math.random() * (maxX - pad);
      const y = pad + Math.random() * (maxY - pad);
      const cover = obs.reduce((sum, o) => sum + overlapArea(x, y, w, h, o), 0);
      if (cover === 0) return { x, y };
      if (!best || cover < best.cover) best = { x, y, cover };
    }
    return best;
  }

  no.addEventListener('click', dodge);
  no.addEventListener('mouseenter', () => { if (count >= 2) dodge(); });

  yes.addEventListener('click', () => {
    for (let i = 0; i < 18; i++) {
      const h = document.createElement('span');
      h.className = 'heart';
      h.textContent = ['💕', '💖', '💗', '❤️'][i % 4];
      h.style.left = Math.random() * 100 + 'vw';
      h.style.top = 80 + Math.random() * 20 + 'vh';
      document.body.appendChild(h);
    }
  });
</script>
<?php page_foot();
