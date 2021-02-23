<?php

namespace controller;

use entity\UtilisateurEntity;
use Exception;
use model\CommandeModel;
use model\ComposerModel;
use model\Model;
use model\ProduitModel;
use model\UtilisateurModel;

class Controller
{
    public function inscription($donnee_u)
    {
        // if form is empty, stop fonction execution
        if (empty($donnee_u)) {
            return;
        }
        $user = new UtilisateurEntity($donnee_u);

        if (($msg = $user->checkData()) === TRUE) {

            $utilisateurModel = new UtilisateurModel();
            if ($utilisateurModel->isInDb($donnee_u)) {
                throw new Exception('Le login n\'est pas disponible');
            } else {
                $utilisateurModel->add($user);
                header('Location: index.php');
            }
        } else {
            throw new Exception(implode('<br />', $msg));
        }
    }

    public function connexion($donnee_u)
    {
        if (isset($donnee_u['login_u']) && isset($donnee_u['motdepass_u'])) {
            $utilisateurModel = new UtilisateurModel();
            $userData = $utilisateurModel->isInDb($donnee_u);
            $checkpassword = password_verify($donnee_u['motdepass_u'], $userData['motdepass_u']);
            if ($userData !== false) {
                if ($checkpassword === true) {
                    $_SESSION['user'] = $userData;
                    header('Location: index.php?page=profil');
                } else {
                    header('Location: index.php?page=home&error=connexion');
                }
            } else {
                header('Location: index.php?page=home&error=connexion');
            }
        }
    }

    public function home()
    {
        if (isset($_GET['error'])) {
            if (($_GET['error'] == 'connexion')) {
                $msg = 'Les identifiants sont incorrects.';
            } elseif ($_GET['error'] == 'accessdenied') {
                $msg = 'Accés refusé.';
            }
            throw new Exception($msg);
        }
    }

    public function categorie()
    {
        $produitModel = new ProduitModel();
        if (isset($_GET['id_sc'])) {
            $id_sc = (int) $_GET['id_sc'];
            $produits = $produitModel->BySous_categorie($id_sc);
        } else {
            $id_c = isset($_GET['id_c']) ? (int) $_GET['id_c'] : 1;
            $produits = $produitModel->ByCategorie($id_c);
        }
        return compact('produits');
    }

    public function produit($input)
    {
        $model = new Model();
        $id_p = isset($_GET['id_p']) ? $_GET['id_p'] : 0;
        $qt_article = isset($_GET['qt_article']) ? $_GET['qt_article'] : 1;
        if (!($produit = $model->getBy($id_p, 'id_p', 'Produit'))) {
            header("Location: index.php?page=404&error=productunfind");
            exit();
        }
        if (isset($input['add'])) {
            // Function to add product in user bag

            //check if user is connected and in all case return ID (temp or final)
            $utilisateurModel = new UtilisateurModel();
            $id_u = $utilisateurModel->getId();

            //Get current order id o create one
            $commandeModel = new CommandeModel;
            if (!($commande = $commandeModel->getBy($id_u, 'id_u'))) {
                $id_com = $commandeModel->add([
                    'id_u' => $id_u,
                    'prix_ttc_com' => 0,
                    'date_com' => date('Y-m-d')
                ]);
                $commande = $commandeModel->getBy($id_com, 'id_com');
            }

            //Add a ligne in composer
            $composerModel = new ComposerModel();
            $composerModel->add([
                'id_com' => $commande->id_com,
                'id_p' => $id_p,
                'qt_article' => $qt_article
            ]);

            header("Location: index.php?page=panier");
            exit();
        }
        return compact('produit');
    }

    public function panier()
    {

        //check if user is connected and in all case return Id (temp or final)
        $utilisateurModel = new UtilisateurModel();
        $id_u = $utilisateurModel->getId();

        //Get current order id 
        $commandeModel = new CommandeModel();;

        //get lignes in composer
        if ($commande = $commandeModel->getBy($id_u, 'id_u')) {
            $composerModel = new ComposerModel();
            $lignes = $composerModel->getAllBy($commande->id_com, 'id_com');
        } else {
            $lignes = NULL;
        }

        return compact('lignes');
    }

    public function profil($donnee_u)
    {
        if (empty($donnee_u)) {
            return;
        }
        if (isset($_SESSION['user'])) {
            $user = new UtilisateurEntity($donnee_u);

            if (($msg = $user->checkData()) === TRUE) {
                $utilisateurModel = new UtilisateurModel();
                $userData = $utilisateurModel->isInDb($donnee_u);

                if ($userData !== false) {
                    throw new Exception('Le login n\'est pas disponible');
                } else {
                    $donnee_u['adresse_u'] = $user->adresse_u;
                    $donnee_u['id_u'] = $_SESSION['user']['id_u'];
                    $donnee_u['motdepass_u'] = password_hash($donnee_u['motdepass_u'], PASSWORD_DEFAULT);
                    $utilisateurModel->editProfil($donnee_u);
                    $_SESSION['user'] = $donnee_u;
                    header('Location: index.php');
                }
            } else {
                throw new Exception(implode('<br />', $msg));
            }
        }
    }
}
