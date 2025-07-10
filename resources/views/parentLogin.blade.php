<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>PHS parent | Log in</title>
      <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
      <link rel="stylesheet" href="{{asset('css/app.css')}}">
      <script type="text/javascript" src="https://ff.kis.v2.scr.kaspersky-labs.com/FD126C42-EBFA-4E12-B309-BB3FDD723AC1/main.js?attr=bhhT50AJpCC71JYrcb7a39MiKplDXrdpWHBxy80aQpMUGfQU7y2gD6fU5_E3nM7omyzy5hyxwlZcouKq1UDlTgoYFAlFhqxToeYYAAMW-cE" charset="UTF-8"></script>
      <link rel="stylesheet" crossorigin="anonymous" href="https://ff.kis.v2.scr.kaspersky-labs.com/E3E8934C-235A-4B0E-825A-35A08381A191/abn/main.css?attr=aHR0cHM6Ly9hZG1pbmx0ZS5pby90aGVtZXMvdjMvcGFnZXMvZXhhbXBsZXMvbG9naW4uaHRtbA"/>
<style>
    /* Existing styles */
    .login-box {
        width: 453px;
        border: 1px solid #e4e4e4;
        background: #fff;
        border-radius: 0;
        position: absolute;
        top: 0;
        right: 0;
        height: 100vh;
    }

    .container-center {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    body{
         background-image: url('{{ asset('images/purplins.jpg') }}'); /* Replace with your actual image path */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        min-height: 100vh;
        margin: 0;
        padding: 0;
    }

    /* New mobile-specific styles */
    @media (max-width: 767px) {
        body {
            margin: 0;
            padding: 0;
        }

        .login-box {
            width: 95%;
            height: 100vh;
            border: none;
            border-radius: 0;
            position: static;
            margin: 10px 5px;
            border-radius: 5px;
        }

        .container-center {
            display: block; /* allow normal flow */
        }

        .login-logo, .login-card-body {
            padding: 20px;
        }
    }
</style>




   </head>
   <body class="hold-transition login-page">
      <div class="login-box container-center">
        
        <div class="" style="width:90%; margin:0 auto;">
             <div class="login-logo">
                <div>
                    <a href="{{route('home')}}" class="font-weight-bold mb-3"> Purplins School Parent's Portal</a>
                </div>
                <img src="{{asset('images/lion_final.png')}}" width="130" alt="">
           
         </div>
         @if ($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
         @endif
         <div>
            <div class=" login-card-body">
               <p class="login-box-msg">Sign in to start your session</p>
               <form action="{{route('parents-login')}}" method="post">
                  <div class="input-group mb-3">
                     <input type="email" name="email" required class="form-control" placeholder="Email">
                     <div class="input-group-append">
                        <div class="input-group-text">
                           <span class="fas fa-envelope"></span>
                        </div>
                     </div>
                  </div>
                  <div class="input-group mb-3">
                     <input type="password" name="password" required class="form-control" placeholder="Password">
                     <div class="input-group-append">
                        <div class="input-group-text">
                           <span class="fas fa-lock"></span>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-8">
                       
                     </div>
                     <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                     </div>
                     @csrf
                  </div>
               </form>
            </div>
         </div>
        </div>
      </div>
      <script src="{{asset('js/app.js')}}"></script>
   </body>
</html>