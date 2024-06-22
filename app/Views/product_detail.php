<!doctype html>
<html>
<head>
<title><?php echo($data['nama']); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?php echo($data['header']); ?>">
<meta property="og:title" content="<?php echo($data['nama']); ?>" />
<meta property="og:url" content="<?php echo base_url()."/".$_SERVER['REQUEST_URI']; ?>" />
<meta property="og:description" content="<?php echo($data['header']); ?>">
<meta property="og:image" content="<?= base_url();?>/uploads/produk/<?= $gambar[0]['nama_file'];?>">
<meta property="og:type" content="article" />
<meta property="og:locale" content="id_ID" />

<meta charset=-"utf-8">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css">
  <link rel="stylesheet" href="<?= base_url();?>/api/public/css/styles.css?<?=time();?>">
</head>
<body> 
<div class="outer">
  <div class="center-wrapper">
    <div class="container-fluid content">
      <div class="row">
        <div class="col-12 col-sm-12 col-md-6 shoe-slider">
          <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
            <?php
                foreach($gambar as $key => $value) {
                    ?>
                    <li data-target="#carouselExampleIndicators" data-slide-to="<?= $key;?>"<?= $value['as_default']=='Y'?' class="active"':'';?>><img src="<?= base_url();?>/uploads/produk/<?= $value['nama_file'];?>" class="d-block w-100" alt="<?= $value['nama_file'];?>"></li>
                    <?php
                }
                ?>
            </ol>
            <div class="carousel-inner">
              <?php
                foreach($gambar as $value) {
                    ?>
                    <div class="carousel-item<?= $value['as_default']=='Y'?' active':'';?>">
                        <img src="<?= base_url();?>/uploads/produk/<?= $value['nama_file'];?>" class="d-block w-100" alt="<?= $value['nama_file'];?>">
                    </div>
                    <?php
                }
              ?>
              <!-- a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
              </a>
              <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
              </a --> 
            </div> 
          </div>
        </div>
        <div class="col-12 col-sm-12 col-md-6 shoe-content">
          <div class="text1">
            <span><?php
              foreach($category as $key => $value) {
                echo ($key>0?', ':'').$value['nama'];
            }
            ?></span>
            <span><?php echo($data['mitra']['city_name']); ?></span>
          </div>
          <div class="text2">
            Rp. <?= number_format($data['harga_jual'], 0 , ',', '.');?>
          </div>
          <div class="text3">
          <?php echo($data['nama']); ?>
          </div> 
          <div class="text4">
            DESKRIPSI PRODUK
          </div> 
          <div class="text5">
            <?php echo($data['penjelasan']); ?>
          </div> 
          <div class="btn-wrapper">
            <a href="https://play.google.com/store/apps/details?id=galerypepi.application.com.galerypepi"><span class="btn">BELI</span></a> 
            
            <!-- span class="qantity">
              <div>
                <i class="fas fa-minus"></i>
                <span class="one">1</span>
                <i class="fas fa-plus"></i>
              </div>
              <div class="quantity-text">QUANTITY</div>
            </span -->

            <!-- div class="social-icons">
              <i class="fab fa-twitter"></i>
              <i class="fab fa-pinterest-p"></i>
              <i class="fab fa-facebook-f"></i>
            </div -->
             
          </div> 
        </div>
      </div>
    </div>
  </div> 
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
