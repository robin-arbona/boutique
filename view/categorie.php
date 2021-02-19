<?php
extract($data);
?>
<div class="container-fluid p-5">
    <h1 class="mt-5 pt-5">Categorie</h1>

    <!-- Tag used to display exception -->
    <?= (isset($msg)) ?  $msg : '' ?>

    <div class="row">
        <?php foreach ($produits as $produit) : ?>
            <div class="col-sm d-flex justify-content-center">
                <div class="card" style="width: 21rem;">
                    <img class="img-card-custom border-img-top" src="public/img/<?= $produit->nom_image_p ?>" alt="...">
                    <div class="card-body">
                        <h5 class="card-title"><?= $produit->nom_p ?></h5>
                        <p class="card-text"><?= $produit->description_p ?></p>
                        <input type="submit" class="btn btn-primary">Ajouter au panier</input>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>