<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CBT result</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="../../dist/css/adminlte.min.css?v=3.2.0">
    <script type="text/javascript"
        src="https://gc.kis.v2.scr.kaspersky-labs.com/FD126C42-EBFA-4E12-B309-BB3FDD723AC1/main.js?attr=lMTY1uLS0OblgVsicSi7iQu2YpDWCHrer9PVa9nHUaQU4fFI8Yza5tRz9rN_0zorXtIPkau1AUjobXT7cwHOTiA1XbSkDCGHxvqBzGWnCJw"
        charset="UTF-8"></script>
    <link rel="stylesheet" crossorigin="anonymous"
        href="https://gc.kis.v2.scr.kaspersky-labs.com/E3E8934C-235A-4B0E-825A-35A08381A191/abn/main.css?attr=aHR0cHM6Ly9hZG1pbmx0ZS5pby90aGVtZXMvdjMvcGFnZXMvZXhhbXBsZXMvbG9naW4uaHRtbA" />
    <script nonce="0148aaa3-c462-4220-9f6b-98995f7c294f">
        (function(w, d) {
            ! function(a, b, c, d) {
                a[c] = a[c] || {};
                a[c].executed = [];
                a.zaraz = {
                    deferred: [],
                    listeners: []
                };
                a.zaraz.q = [];
                a.zaraz._f = function(e) {
                    return async function() {
                        var f = Array.prototype.slice.call(arguments);
                        a.zaraz.q.push({
                            m: e,
                            a: f
                        })
                    }
                };
                for (const g of ["track", "set", "debug"]) a.zaraz[g] = a.zaraz._f(g);
                a.zaraz.init = () => {
                    var h = b.getElementsByTagName(d)[0],
                        i = b.createElement(d),
                        j = b.getElementsByTagName("title")[0];
                    j && (a[c].t = b.getElementsByTagName("title")[0].text);
                    a[c].x = Math.random();
                    a[c].w = a.screen.width;
                    a[c].h = a.screen.height;
                    a[c].j = a.innerHeight;
                    a[c].e = a.innerWidth;
                    a[c].l = a.location.href;
                    a[c].r = b.referrer;
                    a[c].k = a.screen.colorDepth;
                    a[c].n = b.characterSet;
                    a[c].o = (new Date).getTimezoneOffset();
                    if (a.dataLayer)
                        for (const n of Object.entries(Object.entries(dataLayer).reduce(((o, p) => ({
                                ...o[1],
                                ...p[1]
                            })), {}))) zaraz.set(n[0], n[1], {
                            scope: "page"
                        });
                    a[c].q = [];
                    for (; a.zaraz.q.length;) {
                        const q = a.zaraz.q.shift();
                        a[c].q.push(q)
                    }
                    i.defer = !0;
                    for (const r of [localStorage, sessionStorage]) Object.keys(r || {}).filter((t => t.startsWith(
                        "_zaraz_"))).forEach((s => {
                        try {
                            a[c]["z_" + s.slice(7)] = JSON.parse(r.getItem(s))
                        } catch {
                            a[c]["z_" + s.slice(7)] = r.getItem(s)
                        }
                    }));
                    i.referrerPolicy = "origin";
                    i.src = "/cdn-cgi/zaraz/s.js?z=" + btoa(encodeURIComponent(JSON.stringify(a[c])));
                    h.parentNode.insertBefore(i, h)
                };
                ["complete", "interactive"].includes(b.readyState) ? zaraz.init() : a.addEventListener(
                    "DOMContentLoaded", zaraz.init)
            }(w, d, "zarazData", "script");
        })(window, document);
    </script>
    <style>
        .login-page {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url({{asset('images/purplins.jpg')}}) center center no-repeat;
            background-size: cover;
        }
    </style>
</head>

<body class="hold-transition login-page">
   <div class="col-md-10 mx-auto pt-4">
        <div class="card">
            <div class="py-3 text-center"><h4>{{$fullname}}</h4></div>
            <div class="float-right">
               <a href="{{route('cbt-login')}}">Go to Login page</a>
            </div>
            <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Question</th>
                    <th>Corect answer</th>
                    <th>Your answer</th>
                    <th>mark</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $i = 1;
                @endphp
                @foreach ($result as $r)
                    @php
                        $re = (array)$r;
                       @endphp
                    <tr class="@if($r->correct_answer == $r->your_answer) alert-success @else alert-warning @endif" >
                        <td>{{$i++}}</td>
                        <td>{!! $r->question !!}</td>
                        <td>{{isset($re[$r->correct_answer])? $re[$r->correct_answer]: ''}}</td>
                        <td>{{isset($re[$r->your_answer])? $re[$r->your_answer]: ''}}</td>
                        <td>{{$r->correct_answer == $r->your_answer? 2: 0}}</td>
                    </tr>
                @endforeach
            </tbody>
            @php
                $count = 0;
                foreach($result as $r){
                    if($r->correct_answer == $r->your_answer){
                        $count++;
                    }
                }
            @endphp
            <tfoot>
                <tr>
                    <td colspan="4"><h4>Total score</h4></td>
                    <td class="font-weight-bold"><h4><sup>{{$count*2}}</sup>/<sub>{{count($result)*2}}</sub></h4></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
