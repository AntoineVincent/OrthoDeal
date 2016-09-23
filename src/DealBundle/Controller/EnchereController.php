<?php

namespace DealBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Infos;
use DealBundle\Entity\Encheres;
use DealBundle\Form\EnchereType;

class EnchereController extends Controller
{

    public function allEnchereAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$user = $this->container->get('security.context')->getToken()->getUser();

    	$tabenchere = [];

        $encheres = $em->getRepository('DealBundle:Encheres')->findAll();
        foreach($encheres as $enchere) {
        	$product = $em->getRepository('ProductBundle:Produit')->findOneById($enchere->getIdproduit());
        	$fournisseur = $em->getRepository('AppBundle:User')->findOneById($enchere->getIdfournisseur());
        	$commande = $em->getRepository('DealBundle:Commandes')->findOneBy(array(
	        	'idenchere' => $enchere->getId(),
	        	'idacheteur' => $user->getId(),
	        ));
	        if ($commande == NULL) {
	        	$commandeUser = "Pas de commandes";
	        }
	        else {
	        	$commandeUser = $commande->getNbredecommande();
	        }
        	$tabenchere [] = array (
        		'id' => $enchere->getId(),
        		'prix' =>  $enchere->getPrix(),
        		'logoFournisseur' => $fournisseur->getLogo(),
        		'produit' => $product->getNom(),
        		'totalCommande' => $enchere->getTotalcommande(),
        		'commandeUser' => $commandeUser,
        	);
        }


        return $this->render('encheres/all_enchere.html.twig', array(
            'user' => $user,
            'tabenchere' => $tabenchere,
        ));
    }

    public function myEnchereAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$user = $this->container->get('security.context')->getToken()->getUser();

    	$tabenchere = [];
    	$encheres = [];

    	if($user->getType() == "acheteur") {
    		$commandeforSearch = $em->getRepository('DealBundle:Commandes')->findByIdacheteur($user->getId());
    		foreach($commandeforSearch as $com) {
    			$enchere = $em->getRepository('DealBundle:Encheres')->findOneById($com->getIdenchere());
    			array_push($encheres, $enchere);
    		}
    	}
    	elseif ($user->getType() == "fournisseur") {
    		$encheres = $em->getRepository('DealBundle:Encheres')->findByIdfournisseur($user->getId());
    	}



        foreach($encheres as $enchere) {
        	$product = $em->getRepository('ProductBundle:Produit')->findOneById($enchere->getIdproduit());
        	$fournisseur = $em->getRepository('AppBundle:User')->findOneById($enchere->getIdfournisseur());
        	$commande = $em->getRepository('DealBundle:Commandes')->findOneBy(array(
	        	'idenchere' => $enchere->getId(),
	        	'idacheteur' => $user->getId(),
	        ));
	        if ($commande != NULL) {
	        	$commandeUser = $commande->getNbredecommande();
	        }
	        else {
	        	$commandeUser = "Pas de commandes";
	        }
        	$tabenchere [] = array (
        		'id' => $enchere->getId(),
        		'prix' =>  $enchere->getPrix(),
        		'logoFournisseur' => $fournisseur->getLogo(),
        		'produit' => $product->getNom(),
        		'totalCommande' => $enchere->getTotalcommande(),
        		'commandeUser' => $commandeUser,
        	);
        }


        return $this->render('encheres/all_enchere.html.twig', array(
            'user' => $user,
            'tabenchere' => $tabenchere,
        ));
    }

    public function ficheEnchereAction(Request $request, $idenchere)
    {
    	$em = $this->getDoctrine()->getManager();
    	$user = $this->container->get('security.context')->getToken()->getUser();

        $enchere = $em->getRepository('DealBundle:Encheres')->findOneById($idenchere);
        $commande = $em->getRepository('DealBundle:Commandes')->findOneBy(array(
        	'idenchere' => $idenchere,
        	'idacheteur' => $user->getId(),
        ));
        if ($commande == NULL) {
        	$commandeUser = "Pas de commandes";
        }
        else {
        	$commandeUser = $commande->getNbredecommande();
        }
        $product = $em->getRepository('ProductBundle:Produit')->findOneById($enchere->getIdproduit());
        $fournisseur = $em->getRepository('AppBundle:User')->findOneById($enchere->getIdfournisseur());
        $nbreAcheteur = count($em->getRepository('DealBundle:Commandes')->findByIdenchere($idenchere));

        $tabenchere [] = array (
        	'id' => $enchere->getId(),
        	'prix' =>  $enchere->getPrix(),
        	'logoFournisseur' => $fournisseur->getLogo(),
        	'idFournisseur' => $fournisseur->getId(),
        	'cgv' => $fournisseur->getInfos(),
        	'produit' => $product->getNom(),
        	'descProduit' => $product->getDescription(),
        	'photoProduit' => $product->getPhoto(),
        	'totalCommande' => $enchere->getTotalcommande(),
        	'commandeUser' => $commandeUser,
        	'nbreAcheteur' => $nbreAcheteur,
        );

        return $this->render('encheres/fiche_enchere.html.twig', array(
            'user' => $user,
            'tabenchere' => $tabenchere,
        ));
    }

    public function newEnchereAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $enchere = new Encheres();
        $form = $this->createForm('DealBundle\Form\EnchereType', $enchere);
        $form->handleRequest($request);

        $produits = $em->getRepository('ProductBundle:Produit')->findByEtat('non');

        $idprod = $request->request->get('prod');
        $idfournisseur = $request->request->get('fourni');
        $prixMax = $request->request->get('prix');
        $datenotif = $request->request->get('datenotif');

        if($form->isSubmitted() && $form->isValid()) {

            $enchere->setIdproduit($idprod);
            $enchere->setIdfournisseur($idfournisseur);
            $enchere->setPrix($prixMax);

            $prodSelected = $em->getRepository('ProductBundle:Produit')->findOneById($idprod);
            $prodSelected->setEtat('oui');

            // recupere la string date;
            // $date = $enchere->getFulldate()->format('d/m/Y');

            $em->persist($enchere);
            $em->flush();
            $em->persist($prodSelected);
            $em->flush();

            $allUser = $em->getRepository('AppBundle:User')->findAll();
            foreach($allUser as $oneUser) {
                if($oneUser->getType() == "fournisseur") {
                    if($oneUser->getId() == $enchere->getIdfournisseur()){
                        $notif = new Infos();
                        $notif->setIduser($oneUser->getId());
                        $notif->setIdenchere($enchere->getId());
                        $notif->setMessage("Félicitations, la vente pour le produit : ".$prodSelected->getNom()." à bien démarré, vous en êtes le fournisseur de départ.");
                        $notif->setEtat("unread");
                        $notif->setCreatedAt($datenotif);

                        $em->persist($notif);
                        $em->flush();
                    }
                    else {
                        $notif = new Infos();
                        $notif->setIduser($oneUser->getId());
                        $notif->setIdenchere($enchere->getId());
                        $notif->setMessage("Une nouvelle vente à démarré pour le produit : ".$prodSelected->getNom()." vous pouvez y acceder");
                        $notif->setEtat("unread");
                        $notif->setCreatedAt($datenotif);

                        $em->persist($notif);
                        $em->flush();
                    }
                }
                elseif($oneUser->getType() == "acheteur") {
                    $notif = new Infos();
                    $notif->setIduser($oneUser->getId());
                    $notif->setIdenchere($enchere->getId());
                    $notif->setMessage("Une nouvelle vente à démarré pour le produit : ".$prodSelected->getNom()." vous pouvez y acceder");
                    $notif->setEtat("unread");
                    $notif->setCreatedAt($datenotif);

                    $em->persist($notif);
                    $em->flush();
                }
                else {

                }
            }

            return $this->redirectToRoute('fiche_enchere', array('idenchere' => $enchere->getId()));
        }

        return $this->render('encheres/new_enchere.html.twig', array(
            'user' => $user,
            'produits' => $produits,
            'form' => $form->createView(),
        ));
    }

}