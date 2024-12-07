
<!DOCTYPE html>
<html>
    <head>
        <title>PPPOE Connection Details</title>			
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <meta http-equiv="Content-Language" content="en" />
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="expires" content="-1">
        <link rel="stylesheet" href="http://10.168.168.65/assets/plugins/fontawesome-free/css/all.min.css">
        <style type="text/css">
            @import  url('https://fonts.googleapis.com/css?family=Montserrat:400,700');
            @import  url('https://fonts.googleapis.com/css?family=Lato:500');
                * {
                    box-sizing: border-box;
                    font-family: "Source Sans Pro","Segoe UI","Roboto","Helvetica Neue","Arial","sans-serif";
                }

                body {
                    background: #efefef;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    flex-direction: column;
                    font-family: 'Montserrat', sans-serif;
                }

                h2 {
                    text-align: center;
                    color: #3f3f41;
                }

                p {                    
                    font-size: 20px;
                    font-weight: 100;
                    line-height: 20px;
                    margin: 0 0 5px;
                    font-weight: 500;
                    color: #454545;
                }

                span {
                    font-size: 12px;
                }

                a {
                    color: #000;
                    font-size: 14px;
                    text-decoration: none;
                    margin: 15px 0;
                }

                form {
                    background: #ffd89b; 
                    background: -webkit-linear-gradient(to right, #ffd89b, #19547b);
                    background: linear-gradient(to right, #ffd89b, #19547b); 
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-direction: column;
                    padding: 0 0px;
                    height: 100%;
                    text-align: center;
                }

                .white {color: #fff;}

                .input {
                    text-align: left !important;
                    margin-left: -80px;
                }
                .label {
                    font-size: 15px;
                    font-weight: bold;
                }
                .container {
                    background-color: #fff;
                    border-radius: 3px;
                    box-shadow: 0 14px 28px rgba(0,0,0,0.25), 
                            0 10px 10px rgba(0,0,0,0.22);
                    width: 540px;
                    max-width: 100%;
                    min-height: 620px;
                }

                .form-container {
                    position: absolute;
                    top: 0;
                    height: 100%;
                    transition: all 0.6s ease-in-out;
                }

                .sign-in-container {
                    left: 0;
                    width: 100%;
                    z-index: 2;
                }

                .container.right-panel-active .sign-in-container {
                    transform: translateX(100%);
                }

                .container.right-panel-active .sign-up-container {
                    transform: translateX(100%);
                    opacity: 1;
                    z-index: 5;
                    animation: show 0.6s;
                }


                .container.right-panel-active .overlay-right {
                    transform: translateX(20%);
                }

                .social-container {
                    margin: 0px;
                }

                .social-container a {
                    border: 1px solid #fff;
                    border-radius: 50%;
                    display: inline-flex;
                    justify-content: center;
                    align-items: center;
                    margin: 0 5px;
                    height: 40px;
                    width: 40px;
                }
        </style>
    </head>
    <body>	
        <div class="container" id="container">            
            <div class="form-container sign-in-container">
                <form action="#">
                    <img style="margin-top: 20px;" src="http://10.168.168.65/assets/invoice_images/logo.png">
                    <h2 style="color:#dc3545">Internet Payment Warning</h2>
                    <div style="margin-top: 15px;">
                        <p class="white">Your internet services will be temporary suspended on <strong style="color:#000">15 of this month</strong></p>
                        <p class="white" style="margin-top: 25px;">Please pay before due date to use internet.</p>    
                        <p class="white" style="margin-top: 25px;">For more details call at 023-871-200, 012-999-060, 015-505-501</p>
                    </div>
                    <p class="white" style="margin: 30px; font-size: 18px;">Social Connect</p>
                    <div class="social-container white">
                        <a class="white" href="https://www.facebook.com/camkobroadbandofficial" target="_blank" class="social"><i class="fab fa-facebook-f"></i></a>
                        <a class="white" href="https://www.linkedin.com/company/camkocity" target="_blank" class="social"><i class="fab fa-linkedin-in"></i></a>
                        <a class="white" href="https://twitter.com/CityCamko" target="_blank" class="social"><i class="fab fa-twitter"></i></a>
                        <a class="white" href="https://www.youtube.com/@camkocityofficial" target="_blank" class="social"><i class="fab fa-youtube"></i></a>
                    </div>
                    <p class="white" style="margin-top: 25px;">Copyright&copy; by CamKo Broadband. All rights reserved</p>                    
                </form>
            </div>
        </div>
    </body>	
</html>