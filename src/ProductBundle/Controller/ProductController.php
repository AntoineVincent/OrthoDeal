<?php

namespace ProductBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ProductBundle\Entity\Produit;
use ProductBundle\Form\ProductType;

class ProductController extends Controller
{
    public function newProductAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        $product = new Produit();
        $form = $this->createForm('ProductBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($product->getPhoto() != NULL) {
                $photo = $product->getPhoto();

                $photoName = md5(uniqid()).'.'.$photo->guessExtension();
                $photoDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/products';
                $photo->move($photoDir, $photoName);

                $product->setPhoto($photoName);
            }
            $product->setEtat('non');
            $product->setIdfournisseur(0);


            //Create random word like unique ID for make more easy the ajax call in dynamical datatables.
            $len = 12;
            $words = array_merge(range('a', 'z'));
            shuffle($words);
            $uniqId = substr(implode($words), 0, $len);

            $product->setCallname($uniqId);

            $em->persist($product);
            $em->flush();

            $request->getSession()
            ->getFlashBag()
            ->add('success', 'Le Produit à été créé avec succès !')
            ;
        }

        return $this->render('produits/new_product.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }

    public function listProductAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $products = $em->getRepository('ProductBundle:Produit')->findAll();

        foreach($products as $product) {
            $favoris = $em->getRepository('ProductBundle:Favoris')->findBy(array(
                'idproduit' => $product->getId(),
                'idacheteur' => $user->getId()
            ));
            if($favoris != NULL) {
                $favoris = "oui";
            }
            else {
                $favoris = "non";
            }

            $enchere = $em->getRepository('DealBundle:Encheres')->findOneByIdproduit($product->getId());
            if($enchere != NULL && $enchere->getEtat() == "open"){
                $idenchere = $enchere->getId();
                $prix = $enchere->getPrix();
            }
            else {
                $idenchere = "non";
                $prix = $product->getPrixminimal();
            }

            $tabproducts[] = array(
                'id' => $product->getId(),
                'nom' => $product->getNom(),
                'prixminimal' => $prix,
                'commandemaximal' => $product->getCommandemaximal(),
                'favoris' => $favoris,
                'enchere' => $enchere,
                'idenchere' => $idenchere,
                'callback' => $product->getCallname(),
            );
        }

        return $this->render('produits/show_products.html.twig', array(
            'products' => $tabproducts,
            'user' => $user,
        ));
    }
    public function ficheProductAction(Request $request, $idproduct)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $product = $em->getRepository('ProductBundle:Produit')->findOneById($idproduct);

        $enchere = $em->getRepository('DealBundle:Encheres')->findOneByIdproduit($product->getId());
        $enchere = $em->getRepository('DealBundle:Encheres')->findOneBy(array(
            'idproduit' => $product->getId(),
            'etat' => 'open',
        ));

        if($enchere != NULL) {
            $annee = $enchere->getFulldate()->format('Y');
            $mois = $enchere->getFulldate()->format('m');
            $jour = $enchere->getFulldate()->format('d');
        }
        else {
            $annee = "";
            $mois = "";
            $jour = "";
        }

        if($enchere != NULL) {
            $fournisseur = $em->getRepository('AppBundle:User')->findOneById($enchere->getIdfournisseur());

            return $this->render('produits/fiche_product.html.twig', array(
                'product' => $product,
                'user' => $user,
                'enchere' => $enchere,
                'fournisseur' => $fournisseur,
                'annee' => $annee,
                'mois' => $mois,
                'jour' => $jour,
            ));
        }

        return $this->render('produits/fiche_product.html.twig', array(
            'product' => $product,
            'user' => $user,
            'enchere' => $enchere,
        ));
    }
}