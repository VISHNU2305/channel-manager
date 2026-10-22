<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mini Channel Manager</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;800&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root{--ink:#0a0f1f;--glass:rgba(24,32,62,.62);--line:rgba(160,180,255,.16);--text:#eef1f8;--mute:#8b96b8;
--mint:#4de2c5;--amber:#ffc86b;--pink:#ff7aa8;--blue:#6c8cff}
*{box-sizing:border-box;margin:0}
body{font-family:'Noto Sans JP',sans-serif;background:var(--ink);color:var(--text);min-height:100vh;overflow-x:hidden}
h1,h2,.num{font-family:'Sora',sans-serif}
.orb{position:fixed;border-radius:50%;filter:blur(90px);opacity:.35;z-index:-1;animation:drift 18s ease-in-out infinite alternate}
.o1{width:520px;height:520px;background:#3b4fd8;top:-140px;left:-100px}
.o2{width:460px;height:460px;background:#c2377a;bottom:-160px;right:-80px;animation-delay:-6s}
.o3{width:340px;height:340px;background:#0f9c8a;top:40%;left:55%;animation-delay:-11s}
@keyframes drift{to{transform:translate(90px,60px) scale(1.15)}}
.wrap{max-width:1180px;margin:0 auto;padding:32px 20px 60px}
header{display:flex;justify-content:space-between;align-items:end;flex-wrap:wrap;gap:8px;margin-bottom:28px}
h1{font-size:clamp(28px,4vw,44px);font-weight:800;letter-spacing:-.02em}
.jp{color:var(--mute);font-size:15px;margin-top:4px}
.live{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--mute)}
.live i{width:9px;height:9px;border-radius:50%;background:var(--mint);box-shadow:0 0 0 0 var(--mint);animation:ping 2s infinite}
@keyframes ping{70%{box-shadow:0 0 0 10px transparent}}
.glass{background:var(--glass);border:1px solid var(--line);border-radius:20px;backdrop-filter:blur(14px);padding:22px}
.tilt{transition:transform .15s ease-out;transform-style:preserve-3d;will-change:transform}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px;perspective:900px}
.stat .num{font-size:clamp(32px,5vw,52px);font-weight:800;background:linear-gradient(120deg,#fff,var(--blue));-webkit-background-clip:text;color:transparent}
.stat small{color:var(--mute);font-size:13px}
h2{font-size:18px;font-weight:600;margin-bottom:4px}
.sub{color:var(--mute);font-size:13px;margin-bottom:18px}
.stage{perspective:1500px;overflow-x:auto;padding:30px 28px 6px}
.board{min-width:760px;transform-style:preserve-3d;transform:rotateX(calc(14deg + var(--rx,0deg))) rotateY(var(--ry,0deg));transition:transform .2s ease-out}
.row{display:grid;grid-template-columns:150px 1fr;align-items:end;margin-bottom:18px}
.rl{font-size:13px;line-height:1.3;padding-bottom:8px}.rl b{display:block;font-family:'Sora';font-size:14px}.rl span{color:var(--mute)}
.bars{display:grid;grid-template-columns:repeat(14,1fr);gap:8px;height:130px;align-items:end;border-bottom:1px solid var(--line)}
.col{display:flex;flex-direction:column;align-items:center;justify-content:end;height:100%;position:relative}
.bar{width:76%;height:calc(10px + var(--p)*98px);border-radius:4px 4px 0 0;transform-origin:bottom;animation:rise .9s cubic-bezier(.2,1.2,.3,1) both;animation-delay:calc(var(--i)*35ms + var(--r)*250ms);transition:transform .2s,filter .2s;position:relative}
@keyframes rise{from{transform:scaleY(0);opacity:0}}
.bar:hover{transform:translateZ(30px) translateY(-6px);filter:brightness(1.25)}
.ok{background:linear-gradient(var(--mint),#1b9c88);box-shadow:2px -1px #0f6e60,4px -2px #0c5a4f,6px -3px #094a41,8px -4px #073b34}
.low{background:linear-gradient(var(--amber),#d9962a);box-shadow:2px -1px #94651a,4px -2px #7d5415,6px -3px #664411,8px -4px #52360d}
.out{height:8px;background:var(--pink);box-shadow:2px -1px #a8446a,4px -2px #8c3858}
.none{height:4px;background:#2a3358;animation:none}
.bar::after{content:attr(data-tip);position:absolute;bottom:calc(100% + 10px);left:50%;transform:translateX(-50%) translateY(4px);background:#fff;color:#0a0f1f;font-size:12px;padding:5px 9px;border-radius:8px;white-space:nowrap;opacity:0;pointer-events:none;transition:.18s;z-index:5}
.bar:hover::after{opacity:1;transform:translateX(-50%)}
.dl{font-size:11px;color:var(--mute);text-align:center;margin-top:6px;line-height:1.2}.dl b{display:block;color:var(--text);font-weight:500;font-size:12px}
.legend{display:flex;gap:16px;font-size:12px;color:var(--mute);margin-top:14px}
.legend i{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:6px}
.cols{display:grid;grid-template-columns:1fr 1.3fr;gap:22px;margin-top:22px}
label{display:block;font-size:12px;color:var(--mute);margin-bottom:5px}
input,select{width:100%;background:rgba(10,15,31,.6);border:1px solid var(--line);color:var(--text);border-radius:12px;padding:10px 12px;font:inherit;font-size:14px;transition:border .2s,box-shadow .2s}
input:focus,select:focus,button:focus-visible{outline:none;border-color:var(--blue);box-shadow:0 0 0 3px rgba(108,140,255,.3)}
.g3{display:grid;grid-template-columns:1.2fr 1.2fr .8fr;gap:10px}.f{margin-bottom:12px}
button{width:100%;margin-top:6px;padding:12px;border:0;border-radius:12px;font:600 15px 'Sora';color:#fff;cursor:pointer;background:linear-gradient(120deg,#4d6bff,#9a5cff);transition:transform .15s,box-shadow .2s}
button:hover{box-shadow:0 10px 30px rgba(108,140,255,.45);transform:translateY(-2px)}button:active{transform:scale(.97)}
.flash{padding:12px 16px;border-radius:14px;margin-bottom:16px;font-size:14px;animation:slide .5s cubic-bezier(.2,1.2,.3,1)}
@keyframes slide{from{transform:translateY(-14px);opacity:0}}
.good{background:rgba(77,226,197,.14);border:1px solid var(--mint)}.bad{background:rgba(255,122,168,.14);border:1px solid var(--pink)}
table{width:100%;border-collapse:collapse;font-size:13px}th{color:var(--mute);font-weight:400;text-align:left;padding:6px 8px}
td{padding:9px 8px;border-top:1px solid var(--line)}tbody tr{transition:background .2s}tbody tr:hover{background:rgba(255,255,255,.05)}
.pill{padding:2px 9px;border-radius:99px;background:rgba(108,140,255,.2);font-size:12px}
.chgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px;perspective:900px}
.chan{border:1px solid var(--line);border-radius:16px;padding:16px;background:rgba(10,15,31,.45);display:flex;flex-direction:column;gap:6px}
.chn{font:600 16px 'Sora',sans-serif}.fmt{font:400 11px 'Noto Sans JP',sans-serif;color:var(--mute);margin-left:8px}
.chs{font-size:13px;display:flex;align-items:center;gap:8px}
.dot{width:9px;height:9px;border-radius:50%}
.dot.good{background:var(--mint)}.dot.warn{background:var(--amber)}.dot.bad{background:var(--pink)}
.chan small{color:var(--mute);font-size:12px}
.st{padding:2px 9px;border-radius:99px;font-size:12px}
.st.success{background:rgba(77,226,197,.18)}.st.retrying,.st.pending{background:rgba(255,200,107,.2)}.st.failed{background:rgba(255,122,168,.2)}
@media(max-width:820px){.chgrid{grid-template-columns:1fr}}
@media(max-width:820px){.stats,.cols{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
@if (!empty($hasPending))<meta http-equiv="refresh" content="4">@endif
</head>
<body>
<div class="orb o1"></div><div class="orb o2"></div><div class="orb o3"></div>
<div class="wrap">

<header>
  <div>
    <h1>Mini Channel Manager</h1>
    <p class="jp">手間いらず: update once, every booking site stays in sync</p>
  </div>
  <div class="live"><i></i>Inventory locked per night
    <form method="POST" action="{{ route('dashboard.reset') }}" style="margin-left:14px">@csrf
      <button style="width:auto;margin:0;padding:6px 14px;font-size:12px;background:rgba(255,255,255,.1)">Reset demo</button>
    </form>
  </div>
</header>

@if (session('success'))<div class="flash good">{{ session('success') }}</div>@endif
@if (session('error'))<div class="flash bad">{{ session('error') }}</div>@endif
@foreach ($errors->all() as $error)<div class="flash bad">{{ $error }}</div>@endforeach

<div class="stats">
  <div class="glass stat tilt"><div class="num" data-count="{{ $stats['bookings'] }}">0</div><small>Bookings received</small></div>
  <div class="glass stat tilt"><div class="num" data-count="{{ $stats['rooms_booked'] }}">0</div><small>Rooms booked</small></div>
  <div class="glass stat tilt"><div class="num" data-count="{{ $stats['sources'] }}">0</div><small>Booking sources</small></div>
</div>

<section class="glass">
  <h2>Availability, next 14 nights</h2>
  <p class="sub">Taller bar, more rooms left. Move your mouse over the board and hover a bar.</p>
  <div class="stage" id="stage">
    <div class="board" id="board">
      @foreach ($roomTypes as $r => $rt)
      <div class="row">
        <div class="rl"><b>{{ $rt->name }}</b><span>{{ $rt->hotel->name }}</span></div>
        <div>
          <div class="bars">
            @foreach ($dates as $i => $d)
              @php
                $n = ($inventory[$rt->id][$d->toDateString()] ?? null)?->available_count;
                $p = is_null($n) ? 0 : min(1, $n / max(1, $rt->total_rooms));
                $cls = is_null($n) ? 'none' : ($n === 0 ? 'out' : ($p <= .4 ? 'low' : 'ok'));
              @endphp
              <div class="col">
                <div class="bar {{ $cls }}" style="--p:{{ $p }};--i:{{ $i }};--r:{{ $r }}"
                     data-tip="{{ $d->format('M j') }}: {{ is_null($n) ? 'no data' : $n.' left' }}"></div>
              </div>
            @endforeach
          </div>
          @if ($r === 0)
          <div class="bars" style="height:auto;border:0;margin-top:2px">
            @foreach ($dates as $d)<div class="dl"><b>{{ $d->format('j') }}</b>{{ $d->format('D') }}</div>@endforeach
          </div>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
  <div class="legend">
    <span><i style="background:var(--mint)"></i>Plenty left</span>
    <span><i style="background:var(--amber)"></i>Running low</span>
    <span><i style="background:var(--pink)"></i>Sold out</span>
  </div>
</section>

<section class="glass" id="sync-panel" style="margin-top:22px">
  <h2>Channel sync status</h2>
  <p class="sub">Every booking is pushed to the other booking sites by a background queue, with automatic retries.</p>
  <div class="chgrid">
    @foreach ($channels as $ch)
      <div class="chan tilt">
        <div class="chn">{{ $ch['name'] }}<span class="fmt">{{ $ch['format'] }}</span></div>
        <div class="chs"><span class="dot {{ $ch['health'] }}"></span>{{ $ch['label'] }}</div>
        <small>{{ $ch['success'] }} synced, {{ $ch['retrying'] }} retrying, {{ $ch['failed'] }} failed</small>
        <small>Last successful sync: {{ $ch['last'] ?? 'never' }}</small>
      </div>
    @endforeach
  </div>
  <div style="overflow-x:auto">
  <table>
    <thead><tr><th>When</th><th>Booking</th><th>Channel</th><th>Status</th><th>Tries</th><th>Detail</th></tr></thead>
    <tbody>
    @forelse ($recentSync as $s)
      <tr>
        <td style="white-space:nowrap">{{ $s->created_at->diffForHumans() }}</td>
        <td>#{{ $s->booking_id }}</td>
        <td><span class="pill">{{ strtoupper($s->channel) }}</span></td>
        <td><span class="st {{ $s->status }}">{{ $s->status }}</span></td>
        <td>{{ $s->attempts }}</td>
        <td style="color:var(--mute)">{{ \Illuminate\Support\Str::limit($s->message ?? ($s->response_code ? 'HTTP '.$s->response_code : ''), 60) }}</td>
      </tr>
    @empty
      <tr><td colspan="6" style="color:var(--mute)">No syncs yet. Make a booking and watch the channels update.</td></tr>
    @endforelse
    </tbody>
  </table>
  </div>
</section>
<div class="cols">
  <section class="glass">
    <h2>Create a booking</h2>
    <p class="sub">Uses the same locked booking code as the OTA endpoint.</p>
    <form method="POST" action="{{ route('dashboard.book') }}">
      @csrf
      <div class="f"><label>Room type</label>
        <select name="room_type_id">
          @foreach ($roomTypes as $rt)
            <option value="{{ $rt->id }}" @selected(old('room_type_id') == $rt->id)>{{ $rt->hotel->name }}: {{ $rt->name }}</option>
          @endforeach
        </select></div>
      <div class="f"><label>Guest name</label><input name="guest_name" value="{{ old('guest_name', 'Demo Guest') }}"></div>
      <div class="g3 f">
        <div><label>Check-in</label><input type="date" name="check_in" value="{{ old('check_in', now()->addDays(2)->toDateString()) }}"></div>
        <div><label>Check-out</label><input type="date" name="check_out" value="{{ old('check_out', now()->addDays(4)->toDateString()) }}"></div>
        <div><label>Rooms</label><input type="number" min="1" name="rooms" value="{{ old('rooms', 1) }}"></div>
      </div>
      <button>Book now</button>
    </form>
  </section>

  <section class="glass">
    <h2>Latest bookings</h2>
    <p class="sub">Newest first.</p>
    <div style="overflow-x:auto">
    <table>
      <thead><tr><th>#</th><th>Source</th><th>Guest</th><th>Room</th><th>Stay</th><th>Rooms</th></tr></thead>
      <tbody>
      @forelse ($bookings as $b)
        <tr><td>{{ $b->id }}</td><td><span class="pill">{{ $b->source }}</span></td><td>{{ $b->guest_name }}</td>
            <td>{{ $b->roomType->name }}</td><td style="white-space:nowrap">{{ $b->check_in->format('M j') }} to {{ $b->check_out->format('M j') }}</td><td>{{ $b->rooms }}</td></tr>
      @empty
        <tr><td colspan="6" style="color:var(--mute)">No bookings yet. Create one on the left.</td></tr>
      @endforelse
      </tbody>
    </table>
    </div>
  </section>
</div>
</div>

<script>
// count-up numbers
document.querySelectorAll('[data-count]').forEach(el=>{
  const end=+el.dataset.count;let t0=null;
  const step=t=>{t0??=t;const k=Math.min((t-t0)/1200,1);el.textContent=Math.round(end*(1-Math.pow(1-k,3)));if(k<1)requestAnimationFrame(step)};
  requestAnimationFrame(step);
});
// 3D tilt on cards
document.querySelectorAll('.tilt').forEach(c=>{
  c.addEventListener('mousemove',e=>{const r=c.getBoundingClientRect();
    const x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;
    c.style.transform=`rotateX(${-y*14}deg) rotateY(${x*14}deg) translateZ(10px)`});
  c.addEventListener('mouseleave',()=>c.style.transform='');
});
// board follows the mouse
const stage=document.getElementById('stage'),board=document.getElementById('board');
stage.addEventListener('mousemove',e=>{const r=stage.getBoundingClientRect();
  board.style.setProperty('--ry',((e.clientX-r.left)/r.width-.5)*10+'deg');
  board.style.setProperty('--rx',(-((e.clientY-r.top)/r.height-.5))*6+'deg')});
stage.addEventListener('mouseleave',()=>{board.style.setProperty('--ry','0deg');board.style.setProperty('--rx','0deg')});
</script>
</body>
</html>