<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Coobsol Cloud V2.0</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicons -->
    <link href="img/favicon.png" rel="icon">
    <link href="img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,700,700i|Roboto:100,300,400,500,700|Philosopher:400,400i,700,700i" rel="stylesheet">

    <!-- Bootstrap css -->
    <!-- <link rel="stylesheet" href="css/bootstrap.css"> -->
    <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('css/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/owl.theme.default.min.css') }}" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="{{ asset('css/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/modal-video.min.css') }}" rel="stylesheet">

    <!-- Main Stylesheet File -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <!-- =======================================================
      Theme Name: eStartup
      Theme URL: https://bootstrapmade.com/estartup-bootstrap-landing-page-template/
      Author: BootstrapMade.com
      License: https://bootstrapmade.com/license/
    ======================================================= -->
</head>

<body>

<header id="header" class="header header-hide">
    <div class="container">

        <div id="logo" class="pull-left">
            <h1><a href="#body" class="scrollto">Coobsol <span>Cloud</span></a></h1>

            <!-- Uncomment below if you prefer to use an image logo -->
            <!-- <a href="#body"><img src="img/logo.png" alt="" title="" /></a>-->
        </div>

        <nav id="nav-menu-container">
            <ul class="nav-menu">
                <li class="menu-active"><a href="#hero">Home</a></li>
                <li><a href="#about-us">About</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#screenshots">Screenshots</a></li>
                <li><a href="#team">Team</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <!--<li class="menu-has-children"><a href="">Drop Down</a>
                    <ul>
                        <li><a href="#">Drop Down 1</a></li>
                        <li><a href="#">Drop Down 3</a></li>
                        <li><a href="#">Drop Down 4</a></li>
                        <li><a href="#">Drop Down 5</a></li>
                    </ul>
                </li>-->
                <li><a href="#blog">Blog</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav><!-- #nav-menu-container -->
    </div>
</header><!-- #header -->

<!--==========================
  Hero Section
============================-->
<section id="hero" class="wow fadeIn">
    <div class="hero-container">
        <h1></h1>
        <h2>Bienvenido a nuestro sitio web, Apps &amp; mas...</h2>
        <img src="{{ asset('/images/hero-img.png') }}" alt="Hero Imgs">
        <a href="#get-started" class="btn-get-started scrollto">Get Started</a>
        <div class="btns">
            <a href="#"><i class="fa fa-apple fa-3x"></i> App Store</a>
            <a href="#"><i class="fa fa-play fa-3x"></i> Google Play</a>
            <a href="#"><i class="fa fa-windows fa-3x"></i> windows</a>
        </div>
    </div>
</section><!-- #hero -->

<!--==========================
  Get Started Section
============================-->
<section id="get-started" class="padd-section text-center wow fadeInUp">

    <div class="container">
        <div class="section-title text-center">

            <h2>simple systeme fordiscount </h2>
            <p class="separator">Integer cursus bibendum augue ac cursus .</p>

        </div>
    </div>

    <div class="container">
        <div class="row">

            <div class="col-md-6 col-lg-4">
                <div class="feature-block">

                    <img src="{{ asset('/images/svg/cloud.svg') }}" alt="img" class="img-fluid">
                    <h4>introducing whatsapp</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                    <a href="#">read more</a>

                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-block">

                    <img src="{{ asset('/images/svg/planet.svg') }}" alt="img" class="img-fluid">
                    <h4>user friendly interface</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                    <a href="#">read more</a>

                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-block">

                    <img src="{{ asset('/images/svg/asteroid.svg') }}" alt="img" class="img-fluid">
                    <h4>build the app everyone love</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                    <a href="#">read more</a>

                </div>
            </div>

        </div>
    </div>

</section>

<!--==========================
  About Us Section
============================-->
<section id="about-us" class="about-us padd-section wow fadeInUp">
    <div class="container">
        <div class="row justify-content-center">

            <div class="col-md-5 col-lg-3">
                <img src="{{ asset('/images/about-img.png') }}" alt="About">
            </div>

            <div class="col-md-7 col-lg-5">
                <div class="about-content">

                    <h2><span>eStartup</span>UI Design Mobile </h2>
                    <p>Consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat
                    </p>

                    <ul class="list-unstyled">
                        <li><i class="fa fa-angle-right"></i>Creative Design</li>
                        <li><i class="fa fa-angle-right"></i>Retina Ready</li>
                        <li><i class="fa fa-angle-right"></i>Easy to Use</li>
                        <li><i class="fa fa-angle-right"></i>Unlimited Features</li>
                        <li><i class="fa fa-angle-right"></i>Unlimited Features</li>
                    </ul>

                </div>
            </div>

        </div>
    </div>
</section>

<!--==========================
  Features Section
============================-->

<section id="features" class="padd-section text-center wow fadeInUp">

    <div class="container">
        <div class="section-title text-center">
            <h2>Amazing Features.</h2>
            <p class="separator">Integer cursus bibendum augue ac cursus .</p>
        </div>
    </div>

    <div class="container">
        <div class="row">

            <div class="col-md-6 col-lg-3">
                <div class="feature-block">
                    <img src="{{ asset('/images/svg/paint-palette.svg') }}" alt="img" class="img-fluid">
                    <h4>creative design</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-block">
                    <img src="{{ asset('/images/svg/vector.svg') }}" alt="img" class="img-fluid">
                    <h4>Retina Ready</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-block">
                    <img src="{{ asset('/images/svg/design-tool.svg') }}" alt="img" class="img-fluid">
                    <h4>easy to use</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-block">
                    <img src="{{ asset('/images/svg/asteroid.svg') }}" alt="img" class="img-fluid">
                    <h4>Free Updates</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-block">
                    <img src="{{ asset('/images/svg/asteroid.svg') }}" alt="img" class="img-fluid">
                    <h4>Free Updates</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-block">
                    <img src="{{ asset('/images/svg/cloud-computing.svg') }}" alt="img" class="img-fluid">
                    <h4>App store support</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-block">
                    <img src="{{ asset('/images/svg/pixel.svg') }}" alt="img" class="img-fluid">
                    <h4>Perfect Pixel</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-block">
                    <img src="{{ asset('/images/svg/code.svg') }}" alt="img" class="img-fluid">
                    <h4>clean codes</h4>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!--==========================
  Screenshots Section
============================-->
<section id="screenshots" class="padd-section text-center wow fadeInUp">

    <div class="container">
        <div class="section-title text-center">
            <h2>App Gallery</h2>
            <p class="separator">Integer cursus bibendum augue ac cursus .</p>
        </div>
    </div>

    <div class="container">
        <div class="owl-carousel owl-theme">

            <div><img src="{{ asset('/images/screen/1.jpg') }}" alt="img"></div>
            <div><img src="{{ asset('/images/screen/2.jpg') }}" alt="img"></div>
            <div><img src="{{ asset('/images/screen/3.jpg') }}" alt="img"></div>
            <div><img src="{{ asset('/images/screen/4.jpg') }}" alt="img"></div>
            <div><img src="{{ asset('/images/screen/5.jpg') }}" alt="img"></div>
            <div><img src="{{ asset('/images/screen/6.jpg') }}" alt="img"></div>
            <div><img src="{{ asset('/images/screen/7.jpg') }}" alt="img"></div>
            <div><img src="{{ asset('/images/screen/8.jpg') }}" alt="img"></div>
            <div><img src="{{ asset('/images/screen/9.jpg') }}" alt="img"></div>

        </div>
    </div>

</section>

<!--==========================
  Video Section
============================-->

<section id="video" class="text-center wow fadeInUp">
    <div class="overlay">
        <div class="container-fluid container-full">

            <div class="row">
                <a href="#" class="js-modal-btn play-btn" data-video-id="rkHv5vATzS0"></a>
            </div>

        </div>
    </div>
</section>

<!--==========================
  Team Section
============================-->
<section id="team" class="padd-section text-center wow fadeInUp">

    <div class="container">
        <div class="section-title text-center">

            <h2>Team Member</h2>
            <p class="separator">Integer cursus bibendum augue ac cursus .</p>

        </div>
    </div>

    <div class="container">
        <div class="row">

            <div class="col-md-6 col-md-4 col-lg-3">
                <div class="team-block bottom">
                    <img src="{{ asset('/images/team/1.jpg') }}" class="img-responsive" alt="img">
                    <div class="team-content">
                        <ul class="list-unstyled">
                            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        </ul>
                        <span>manager</span>
                        <h4>Kimberly Tran</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-md-4 col-lg-3">
                <div class="team-block bottom">
                    <img src="{{ asset('/images/team/2.jpg') }}" class="img-responsive" alt="img">
                    <div class="team-content">
                        <ul class="list-unstyled">
                            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        </ul>
                        <span>manager</span>
                        <h4>Kimberly Tran</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-md-4 col-lg-3">
                <div class="team-block bottom">
                    <img src="{{ asset('/images/team/3.jpg') }}" class="img-responsive" alt="img">
                    <div class="team-content">
                        <ul class="list-unstyled">
                            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        </ul>
                        <span>manager</span>
                        <h4>Kimberly Tran</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-md-4 col-lg-3">
                <div class="team-block bottom">
                    <img src="{{ asset('/images/team/4.jpg') }}" class="img-responsive" alt="img">
                    <div class="team-content">
                        <ul class="list-unstyled">
                            <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                            <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                            <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        </ul>
                        <span>manager</span>
                        <h4>Kimberly Tran</h4>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


<!--==========================
  Testimonials Section
============================-->

<section id="testimonials" class="padd-section text-center wow fadeInUp">
    <div class="container">
        <div class="row justify-content-center">

            <div class="col-md-8">

                <div class="testimonials-content">
                    <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">

                        <div class="carousel-inner" role="listbox">

                            <div class="carousel-item  active">
                                <div class="top-top">

                                    <h2>Our Users Speack volumes us</h2>
                                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type
                                        specimen book. It has survived not only five centuries.</p>
                                    <h4>Kimberly Tran<span>manager</span></h4>

                                </div>
                            </div>

                            <div class="carousel-item ">
                                <div class="top-top">

                                    <h2>Our Users Speack volumes us</h2>
                                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type
                                        specimen book. It has survived not only five centuries.</p>
                                    <h4>Henderson<span>manager</span></h4>

                                </div>
                            </div>

                            <div class="carousel-item ">
                                <div class="top-top">

                                    <h2>Our Users Speack volumes us</h2>
                                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type
                                        specimen book. It has survived not only five centuries.</p>
                                    <h4>David Spark<span>manager</span></h4>

                                </div>
                            </div>

                        </div>

                        <div class="btm-btm">

                            <ul class="list-unstyled carousel-indicators">
                                <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                                <li data-target="#carousel-example-generic" data-slide-to="1"></li>
                                <li data-target="#carousel-example-generic" data-slide-to="2"></li>
                            </ul>

                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!--==========================
  Pricing Table Section
============================-->
<section id="pricing" class="padd-section text-center wow fadeInUp">

    <div class="container">
        <div class="section-title text-center">

            <h2>Meet With Price</h2>
            <p class="separator">Integer cursus bibendum augue ac cursus .</p>

        </div>
    </div>

    <div class="container">
        <div class="row">

            <div class="col-md-6 col-lg-3">
                <div class="block-pricing">
                    <div class="table">
                        <h4>basic</h4>
                        <h2>$29</h2>
                        <ul class="list-unstyled">
                            <li><b>4 GB</b> Ram</li>
                            <li><b>7/24</b> Tech Support</li>
                            <li><b>40 GB</b> SSD Cloud Storage</li>
                            <li>Monthly Backups</li>
                            <li>Palo Protection</li>
                        </ul>
                        <div class="table_btn">
                            <a href="#" class="btn"><i class="fa fa-shopping-cart"></i> Buy Now</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="block-pricing">
                    <div class="table">
                        <h4>PERSONAL</h4>
                        <h2>$29</h2>
                        <ul class="list-unstyled">
                            <li><b>4 GB</b> Ram</li>
                            <li><b>7/24</b> Tech Support</li>
                            <li><b>40 GB</b> SSD Cloud Storage</li>
                            <li>Monthly Backups</li>
                            <li>Palo Protection</li>
                        </ul>
                        <div class="table_btn">
                            <a href="#" class="btn"><i class="fa fa-shopping-cart"></i> Buy Now</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="block-pricing">
                    <div class="table">
                        <h4>BUSINESS</h4>
                        <h2>$29</h2>
                        <ul class="list-unstyled">
                            <li><b>4 GB</b> Ram</li>
                            <li><b>7/24</b> Tech Support</li>
                            <li><b>40 GB</b> SSD Cloud Storage</li>
                            <li>Monthly Backups</li>
                            <li>Palo Protection</li>
                        </ul>
                        <div class="table_btn">
                            <a href="#" class="btn"><i class="fa fa-shopping-cart"></i> Buy Now</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="block-pricing">
                    <div class="table">
                        <h4>profeesional</h4>
                        <h2>$29</h2>
                        <ul class="list-unstyled">
                            <li><b>4 GB</b> Ram</li>
                            <li><b>7/24</b> Tech Support</li>
                            <li><b>40 GB</b> SSD Cloud Storage</li>
                            <li>Monthly Backups</li>
                            <li>Palo Protection</li>
                        </ul>
                        <div class="table_btn">
                            <a href="#" class="btn"><i class="fa fa-shopping-cart"></i> Buy Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!--==========================
  Blog Section
============================-->
<section id="blog" class="padd-section wow fadeInUp">

    <div class="container">
        <div class="section-title text-center">

            <h2>Latest posts</h2>
            <p class="separator">Integer cursus bibendum augue ac cursus .</p>

        </div>
    </div>

    <div class="container">
        <div class="row">

            <div class="col-md-6 col-lg-4">
                <div class="block-blog text-left">
                    <a href="#"><img src="{{ asset('/images/blog/blog-image-1.jpg') }}" alt="img"></a>
                    <div class="content-blog">
                        <h4><a href="#">whats isthe difference between good and bat typography</a></h4>
                        <span>05, juin 2017</span>
                        <a class="pull-right readmore" href="#">read more</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="block-blog text-left">
                    <a href="#"><img src="{{ asset('/images/blog/blog-image-2.jpg') }}" class="img-responsive" alt="img"></a>
                    <div class="content-blog">
                        <h4><a href="#">whats isthe difference between good and bat typography</a></h4>
                        <span>05, juin 2017</span>
                        <a class="pull-right readmore" href="#">read more</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="block-blog text-left">
                    <a href="#"><img src="{{ asset('/images/blog/blog-image-1.jpg') }}" class="img-responsive" alt="img"></a>
                    <div class="content-blog">
                        <h4><a href="#">whats isthe difference between good and bat typography</a></h4>
                        <span>05, juin 2017</span>
                        <a class="pull-right readmore" href="#">read more</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!--==========================
  Newsletter Section
============================-->
<section id="newsletter" class="newsletter text-center wow fadeInUp">
    <div class="overlay padd-section">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-md-9 col-lg-6">
                    <form class="form-inline" method="POST" action="#">

                        <input type="email" class="form-control " placeholder="Email Adress" name="email">
                        <button type="submit" class="btn btn-default"><i class="fa fa-location-arrow"></i>Subscribe</button>

                    </form>

                </div>
            </div>

            <ul class="list-unstyled">
                <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
                <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
            </ul>


        </div>
    </div>
</section>

<!--==========================
  Contact Section
============================-->
<section id="contact" class="padd-section wow fadeInUp">

    <div class="container">
        <div class="section-title text-center">
            <h2>Contact</h2>
            <p class="separator">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">

            <div class="col-lg-3 col-md-4">

                <div class="info">
                    <div>
                        <i class="fa fa-map-marker"></i>
                        <p>A108 Adam Street<br>New York, NY 535022</p>
                    </div>

                    <div class="email">
                        <i class="fa fa-envelope"></i>
                        <p>info@example.com</p>
                    </div>

                    <div>
                        <i class="fa fa-phone"></i>
                        <p>+1 5589 55488 55s</p>
                    </div>
                </div>

                <div class="social-links">
                    <a href="#" class="twitter"><i class="fa fa-twitter"></i></a>
                    <a href="#" class="facebook"><i class="fa fa-facebook"></i></a>
                    <a href="#" class="instagram"><i class="fa fa-instagram"></i></a>
                    <a href="#" class="google-plus"><i class="fa fa-google-plus"></i></a>
                    <a href="#" class="linkedin"><i class="fa fa-linkedin"></i></a>
                </div>

            </div>

            <div class="col-lg-5 col-md-8">
                <div class="form">
                    <div id="sendmessage">Your message has been sent. Thank you!</div>
                    <div id="errormessage"></div>
                    <form action="" method="post" role="form" class="contactForm">
                        <div class="form-group">
                            <input type="text" name="name" class="form-control" id="name" placeholder="Your Name" data-rule="minlen:4" data-msg="Please enter at least 4 chars" />
                            <div class="validation"></div>
                        </div>
                        <div class="form-group">
                            <input type="email" class="form-control" name="email" id="email" placeholder="Your Email" data-rule="email" data-msg="Please enter a valid email" />
                            <div class="validation"></div>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="subject" id="subject" placeholder="Subject" data-rule="minlen:4" data-msg="Please enter at least 8 chars of subject" />
                            <div class="validation"></div>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" name="message" rows="5" data-rule="required" data-msg="Please write something for us" placeholder="Message"></textarea>
                            <div class="validation"></div>
                        </div>
                        <div class="text-center"><button type="submit">Send Message</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section><!-- #contact -->

<!--==========================
  Footer
============================-->
<footer class="footer">
    <div class="container">
        <div class="row">

            <div class="col-md-12 col-lg-4">
                <div class="footer-logo">

                    <a class="navbar-brand" href="#">eStartup</a>
                    <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the.</p>

                </div>
            </div>

            <div class="col-sm-6 col-md-3 col-lg-2">
                <div class="list-menu">

                    <h4>Abou Us</h4>

                    <ul class="list-unstyled">
                        <li><a href="#">About us</a></li>
                        <li><a href="#">Features item</a></li>
                        <li><a href="#">Live streaming</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>

                </div>
            </div>

            <div class="col-sm-6 col-md-3 col-lg-2">
                <div class="list-menu">

                    <h4>Abou Us</h4>

                    <ul class="list-unstyled">
                        <li><a href="#">About us</a></li>
                        <li><a href="#">Features item</a></li>
                        <li><a href="#">Live streaming</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>

                </div>
            </div>

            <div class="col-sm-6 col-md-3 col-lg-2">
                <div class="list-menu">

                    <h4>Support</h4>

                    <ul class="list-unstyled">
                        <li><a href="#">faq</a></li>
                        <li><a href="#">Editor help</a></li>
                        <li><a href="#">Contact us</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>

                </div>
            </div>

            <div class="col-sm-6 col-md-3 col-lg-2">
                <div class="list-menu">

                    <h4>Abou Us</h4>

                    <ul class="list-unstyled">
                        <li><a href="#">About us</a></li>
                        <li><a href="#">Features item</a></li>
                        <li><a href="#">Live streaming</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>

                </div>
            </div>

        </div>
    </div>

    <div class="copyrights">
        <div class="container">
            <p>&copy; Copyrights eStartup. All rights reserved.</p>
            <div class="credits">
                <!--
                  All the links in the footer should remain intact.
                  You can delete the links only if you purchased the pro version.
                  Licensing information: https://bootstrapmade.com/license/
                  Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/buy/?theme=eStartup
                -->
                Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
            </div>
        </div>
    </div>

</footer>



<a href="#" class="back-to-top"><i class="fa fa-chevron-up"></i></a>
<!-- JavaScript Libraries -->
<script src="{{ asset('/js/jquery.min.js') }}"></script>
<script src="{{ asset('/js/jquery-migrate.min.js') }}"></script>
<script src="{{ asset('/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('/js/hoverIntent.js') }}"></script>
<script src="{{ asset('/js/superfish.min.js') }}"></script>
<script src="{{ asset('/js/easing.min.js') }}"></script>
<script src="{{ asset('/js/modal-video.min.js') }}"></script>
<script src="{{ asset('/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('/js/wow.min.js') }}"></script>
<script src="{{ asset('/js/contactform.js') }}"></script>
<script src="{{ asset('/js/main.js') }}"></script>

</body>
</html>
