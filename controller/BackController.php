<?php

namespace controller;

use Exception;
use library\FileHandeler;
use model\Model;

class BackController
{
    public function dashboard()
    {


        $model = new Model();

        $utilisateur = $model->getAll('Utilisateur');
        $categorie = $model->getAll('Categorie');
        $sous_categorie = $model->getAll('Sous_categorie');
        $composer = $model->getAll('Composer');
        $produit = $model->getAll('Produit');
        $commande = $model->getAll('Commande');
        $configuration = $model->getAll('Configuration');


        return compact('utilisateur', 'categorie', 'sous_categorie', 'composer', 'produit', 'commande', 'configuration');
    }

    public function add($data)
    {
        if (isset($_GET['table'])) {
            $model = new Model();
            $table = ucfirst($_GET['table']);
            $model->add($data, $table);
        }
    }

    public function editDeleteDb($data)
    {
        if (isset($_GET['table'])) {
            $model = new Model();
            $table = ucfirst($_GET['table']);
            $action = $data['submit'];
            unset($data['submit']);
            if ($action == 'edit') {
                $model->edit($data, $table);
            } elseif ($action == 'delete') {
                $model->delete($data, $table);
            }
            header('Location: index.php?page=backoffice&pane=dashboard');
        }
    }

    public function uploadFile($data)
    {
        if (empty($data)) {
            return;
        }
        $fileHandeler = new FileHandeler();
        if (($msg = $fileHandeler->upload()) === TRUE) {
            header('Location: index.php?page=backoffice&pane=image');
        } else {
            throw new Exception(implode("<br/>", $msg));
        }
    }

    public function editDeleteFile($data)
    {
        if (empty($data)) {
            return;
        }
        $action = $data['submit'];
        unset($data['submit']);
        $fileHandeler = new FileHandeler();

        if ($action == 'edit') {
            $msg = $fileHandeler->edit($data);
        } elseif ($action == 'delete') {
            $msg = $fileHandeler->delete($data);
        }
        if ($msg === TRUE) {
            header('Location: index.php?page=backoffice&pane=image');
        } else {
            throw new Exception($msg);
        }
    }

    public function producteditor($data)
    {
        $model = new Model();
        $model->edit([
            "id_p" => $data["id_p"],
            "description_p" => $data["description_p"]
        ], 'Produit');
        header('Location: index.php?page=backoffice&pane=producteditor');
    }

    public function homepageeditor($data)
    {
        $model = new Model();
        var_dump($data);
        foreach ($data as $key => $value) {
            $model->edit([
                "id" => $key,
                "value" => $value
            ], 'Configuration');
        }
        header('Location: index.php?page=backoffice&pane=homepageeditor');
    }
}
