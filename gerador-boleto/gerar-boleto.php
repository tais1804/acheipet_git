<?php

/**********************************************************************
 * ********************************************************************
 * CAMADA PRINCIPAL MAYKONSILVEIRA.COM.BR E MAYKON SILVEIRA
 * 
 * ********************************************************************
* MAYKONSILVEIRA.COM.BR DEREICIONANDO VOCÊ PARA O CAMINHO DO SUCESSO #*
 * *************MAYKON***SILVEIRA**************************************
 * *************sheep**TECHNOLOGIES***********************************
 * ********************************************************************
 *
 * ********************************************************************
 * ********************************************************************
 */
ob_start();
require('./sheep_core/config.php');
?>
<!DOCTYPE html>
<html lang="pt-br" >
<head >
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Maykon Silveira</title>
        <link rel="stylesheet" href="assets/css/app.min.css">
      
        <link rel="stylesheet" href="assets/css/style.css">
        <!-- FIM DO CSS  SHEEP FRAMEWORK PHP - MAYKONSILVEIRA.COM.BR -->
</head>
<body>


<!-- Main Content -->
<div align="center" style="padding:20px; margin-top:120px;" >
 
        <div class="col-md-10"> 
      <section class="section" >


            <!-- inicio topo menu -->
            <?php
            
            require_once('topo.php');

            ?>
      
            <!-- fim topo menu -->


           <br>
         <!-- inicio formulario  topo menu -->
          <form action="" method="post" enctype="multipart/form-data">


         <div class="section-body" >
          <div class="row" >
            <div class="col-md-12">
              <div class="card">
                  
                    
                <div class="card-header">
                  <h4>Gerar Boletos</h4><br>
                 
                </div>
                <div class="card-body">
         
                  <div class="form-group row mb-4">
                   
                    <div class="col-md-12">
                      <input type="date" class="form-control" name="data">
                    </div>
                    
                  </div>

                  <div class="form-group row mb-4">
                   
                   <div class="col-md-12">
                     
                     <select name="id" class="form-control">
                       <option value="">Maykon Silveira</option>
                       <option value="">Naty Silveira</option>
                     </select>
                   </div>
                   
                 </div>


                 <div class="form-group row mb-4">
                   
                    <div class="col-md-12">
                      <input type="text" class="form-control" name="plano" placeholder="Nome do plano">
                    </div>
                    
                  </div>


                  <div class="form-group row mb-4">
                   
                    <div class="col-md-12">
                      <input type="number" class="form-control" name="plano" placeholder="Valor">
                    </div>
                    
                  </div>

              
                

                  
                  <div class="form-group row mb-4">
                   
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-lg btn-primary"  style="width:100%;" id="button" >Gerar</button>
                    </div>

                  </div>
                  <p><a href="https://maykonsilveira.com.br">EAD MAykon Silveira</a></p>
                </div>
              </div>
            </div>
          </div>
        </div>
            </form>
      <!-- fim formulario  topo menu -->
      </section>
      </div>
        
       
    </div>

  <script src="assets/js/custom.js"></script>

 
  

</body>
</html>

<?php
ob_end_flush();
?>