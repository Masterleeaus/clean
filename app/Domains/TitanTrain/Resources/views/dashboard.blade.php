<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Titan Train — {{ $company->name }}</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; background:#0b1020; color:#edf2ff; }
        * { box-sizing:border-box; } body { margin:0; background:linear-gradient(145deg,#0b1020,#131c34); min-height:100vh; }
        header { position:sticky; top:0; padding:16px 22px; background:rgba(11,16,32,.94); border-bottom:1px solid #2a3552; backdrop-filter:blur(12px); }
        main { max-width:1120px; margin:auto; padding:24px; } h1,h2,h3,p { margin-top:0; }
        .chat { background:#151f39; border:1px solid #344368; border-radius:18px; padding:16px 18px; margin:18px 0 24px; color:#aab8d8; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:16px; }
        .card { background:#111a30; border:1px solid #2a375a; border-radius:18px; padding:18px; box-shadow:0 10px 28px rgba(0,0,0,.18); }
        .metric { font-size:30px; font-weight:800; } .muted { color:#9eabd0; } .ok { color:#69e4ad; } .warn { color:#ffd36a; }
        .bar { height:10px; background:#202d4b; border-radius:99px; overflow:hidden; margin:10px 0; } .bar span { display:block; height:100%; background:linear-gradient(90deg,#6ea8ff,#8b7dff); }
        .pill { display:inline-block; border:1px solid #435785; border-radius:999px; padding:5px 10px; margin:3px 4px 3px 0; font-size:12px; }
        table { width:100%; border-collapse:collapse; } th,td { text-align:left; padding:12px 8px; border-bottom:1px solid #263250; }
        a,button { color:#fff; background:#576eff; border:0; border-radius:12px; padding:10px 14px; text-decoration:none; cursor:pointer; }
    </style>
</head>
<body>
<header><strong>Titan Train</strong> <span class="muted">/ {{ $company->name }}</span></header>
<main>
    <h1>Cleaner training</h1>
    <div class="chat">Ask Titan Zero: “What training do I need next?”, “Show my cleaner foundation progress”, or “Am I ready for work?”</div>

    <div class="grid">
        <section class="card">
            <h2>My readiness</h2>
            <div class="metric {{ ($myReadiness['ready'] ?? false) ? 'ok' : 'warn' }}">{{ ($myReadiness['ready'] ?? false) ? 'Ready' : ucfirst(str_replace('_',' ', $myReadiness['status'] ?? 'not assigned')) }}</div>
            <div class="bar"><span style="width:{{ (int)($myReadiness['progress_percent'] ?? 0) }}%"></span></div>
            <p class="muted">{{ (int)($myReadiness['progress_percent'] ?? 0) }}% learning progress</p>
            <span class="pill">Knowledge: {{ ($myReadiness['knowledge_passed'] ?? false) ? 'passed' : 'pending' }}</span>
            <span class="pill">Practical: {{ ($myReadiness['practical_passed'] ?? false) ? 'passed' : 'pending' }}</span>
        </section>
        <section class="card">
            <h2>Cleaner foundation</h2>
            @if($program)
                <p><strong>{{ $program->title }}</strong></p>
                <p class="muted">9 modules · 26 lessons · 2 assessments · 5 competencies</p>
                <span class="pill">{{ $program->status }}</span>
            @else
                <p class="warn">Not installed for this company.</p>
                <p class="muted">Run the setup command or management API to install it.</p>
            @endif
        </section>
        <section class="card">
            <h2>Chatbot PWA</h2>
            <p class="muted">Titan Train is exposed through the authenticated online API and PWA bridge.</p>
            <a href="/chatbot-pwa/apps/titan-train.json">Open app manifest</a>
        </section>
    </div>

    <h2 style="margin-top:28px">My assignments</h2>
    <div class="grid">
        @forelse($assignments as $assignment)
            <section class="card">
                <h3>{{ $assignment->program->title }}</h3>
                <div class="bar"><span style="width:{{ $assignment->progress_percent }}%"></span></div>
                <p>{{ $assignment->progress_percent }}% · {{ str_replace('_',' ', $assignment->status) }}</p>
                <p class="muted">Due {{ optional($assignment->due_at)->format('j M Y') ?? 'not set' }}</p>
            </section>
        @empty
            <section class="card"><p class="muted">No training has been assigned yet.</p></section>
        @endforelse
    </div>

    @if($manage)
        <h2 style="margin-top:28px">Cleaner readiness</h2>
        <section class="card" style="overflow:auto">
            <table>
                <thead><tr><th>Worker</th><th>Role</th><th>Progress</th><th>Status</th><th>Ready</th></tr></thead>
                <tbody>
                @foreach($cleaners as $row)
                    <tr>
                        <td>{{ $row['user']->name }} {{ $row['user']->surname }}</td>
                        <td>{{ $row['member']->role_key }}</td>
                        <td>{{ (int)($row['readiness']['progress_percent'] ?? 0) }}%</td>
                        <td>{{ str_replace('_',' ', $row['readiness']['status'] ?? 'not assigned') }}</td>
                        <td>{{ ($row['readiness']['ready'] ?? false) ? 'Yes' : 'No' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    @endif
</main>
<script src="/chatbot-pwa/apps/titan-train.js"></script>
</body>
</html>
