<?php

namespace DealBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Infos;
use DealBundle\Entity\Encheres;
use DealBundle\Entity\Commandes;
use DealBundle\Form\EnchereType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class EnchereController extends Controller
{

    public function allEnchereAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$user = $this->container->get('security.context')->getToken()->getUser();

    	$tabenchere = [];

        $encheres = $em->getRepository('DealBundle:Encheres')->findByEtat("open");
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
            $com = $enchere->getPrix() * 0.2;
            $mtnCom = $enchere->getTotalcommande() * $com;

        	$tabenchere [] = array (
        		'id' => $enchere->getId(),
        		'prix' =>  $enchere->getPrix(),
        		'logoFournisseur' => $fournisseur->getLogo(),
        		'produit' => $product->getNom(),
        		'totalCommande' => $enchere->getTotalcommande(),
        		'commandeUser' => $commandeUser,
                'annee' => $enchere->getFulldate()->format('Y'),
                'mois' => $enchere->getFulldate()->format('m'),
                'jour' => $enchere->getFulldate()->format('d'),
                'minicom' => $product->getCommandemaximal(),
                'mtnCom' => $mtnCom,
                'valCom' => $com,
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
                $enchere = $em->getRepository('DealBundle:Encheres')->findOneBy(array(
                    'id' => $com->getIdenchere(),
                    'etat' => "open",
                ));
                if($enchere != NULL) {
    			 array_push($encheres, $enchere);
                }
    		}
    	}
    	elseif ($user->getType() == "fournisseur") {
            $encheres = $em->getRepository('DealBundle:Encheres')->findBy(array(
                'etat' => "open",
                'idfournisseur' => $user->getId(),
            ));
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
                'annee' => $enchere->getFulldate()->format('Y'),
                'mois' => $enchere->getFulldate()->format('m'),
                'jour' => $enchere->getFulldate()->format('d'),
                'minicom' => $product->getCommandemaximal(),
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
            'etat' => $enchere->getEtat(),
            'annee' => $enchere->getFulldate()->format('Y'),
            'mois' => $enchere->getFulldate()->format('m'),
            'jour' => $enchere->getFulldate()->format('d'),
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

        $produits = $em->getRepository('ProductBundle:Produit')->findByEtat('non');

        $idprod = $request->request->get('prod');
        $idfournisseur = $request->request->get('fourni');
        $prixfourni = $request->request->get('prix');
        $datenotif = $request->request->get('datenotif');

        $com = $prixfourni * 0.2;
        $prixVente = $prixfourni + $com;

        if($idprod != NULL) {
            $datenew = $request->request->get('datenew');
            $dateNew = new \DateTime($datenew);
            $datemid = $request->request->get('datemid');
            $dateMid = new \DateTime($datemid);
            $dateold = $request->request->get('dateold');
            $dateOld = new \DateTime($dateold);
            $fulldate = $request->request->get('fulldate');
            $fulldate = new \DateTime($fulldate);

            $enchere = new Encheres();

            $enchere->setIdproduit($idprod);
            $enchere->setIdfournisseur($idfournisseur);
            $enchere->setCommission($com);
            $enchere->setBeneffourni($prixfourni);
            $enchere->setEtat('open');
            $enchere->setDatenew($dateNew);
            $enchere->setDatemid($dateMid);
            $enchere->setDateold($dateOld);
            $enchere->setFulldate($fulldate);
            $enchere->setEtatnew("new");

            $prodSelected = $em->getRepository('ProductBundle:Produit')->findOneById($idprod);
            $prodSelected->setEtat('oui');
            $enchere->setPrix($prixVente);

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

                    $verifFav = $em->getRepository('ProductBundle:Favoris')->findBy(array(
                        'idacheteur' => $oneUser->getId(),
                        'idproduit' => $idprod,
                    ));

                    if($verifFav != NULL) {

                        $message = \Swift_Message::newInstance()
                            ->setSubject('CANEO : Nouvelle vente')//objet du mail
                            ->setFrom(array('anton51200@laposte.net' => 'CANEO Website[Do not reply]')) //adresse expéditeur
                            //->setReadReceiptTo('ninon.pelaez@gmail.com') //accusé de réception
                            ->setTo($oneUser->getEmailCanonical()) //adresse du cabinet qui commande
                            // ->setTo('anton071192@gmail.com') //adresse du cabinet qui commande
                            ->setCharset('utf-8')
                            ->setContentType('text/html')
                            //corps du texte : valeurs à appeler dans la vue mail_cabinet.html.twig
                            ->setBody("Une nouvelle vente à démarré pour le produit : ".$prodSelected->getNom().". Vous pouvez y acceder directement depuis votre espace.");
                        $this->get('mailer')->send($message); //action d'envoi
                    }
                }
                else {

                }
            }

            return $this->redirectToRoute('fiche_enchere', array('idenchere' => $enchere->getId()));
        }

        return $this->render('encheres/new_enchere.html.twig', array(
            'user' => $user,
            'produits' => $produits,
        ));
    }
    public function enchereUpAction(Request $request, $idenchere) 
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();


        $enchere = $em->getRepository('DealBundle:Encheres')->findOneById($idenchere);
        $oldfourni = $enchere->getIdfournisseur();
        $oldprice = $enchere->getPrix();

        $idprod = $enchere->getIdproduit();
        $prodSelected = $em->getRepository('ProductBundle:Produit')->findOneById($idprod);

        $newPrice = $request->request->get('newprice');
        $prixfourni = $request->request->get('total');
        $commission = $request->request->get('commission');
        $datenotif = $request->request->get('datenotif');

        if($newPrice != NULL) {
            if($newPrice >= $enchere->getPrix()) {
                $request->getSession()
                ->getFlashBag()
                ->add('danger', 'Le nouveau prix n\'est pas inférieur à l\'ancien, veuillez rentrer un prix valide !')
                ;

                return $this->redirectToRoute('fiche_enchere', array('idenchere' => $idenchere));
            }
            else {
                $enchere->setPrix($newPrice);
                $enchere->setCommission($commission);
                $enchere->setBeneffourni($prixfourni);
                $enchere->setIdfournisseur($user->getId());

                $em->persist($enchere);
                $em->flush();

                $notif = new Infos();
                $notif->setIduser($oldfourni);
                $notif->setIdenchere($enchere->getId());
                $notif->setMessage("Vous n'êtes plus le fournisseur de la vente n°".$enchere->getId()." le nouveau prix est de ".$enchere->getPrix()."€ au lieu de ".$oldprice."€ . Vous pouvez acceder à la vente ");
                $notif->setEtat("unread");
                $notif->setCreatedAt($datenotif);
                $em->persist($notif);
                $em->flush();

                $favProd = $em->getRepository('ProductBundle:Favoris')->findByIdproduit($idprod);
                if($favProd != NULL) {
                    foreach($favProd as $fav) {
                        $user = $em->getRepository('AppBundle:User')->findOneById($fav->getIdacheteur());

                        $notif = new Infos();
                            $notif->setIduser($user->getId());
                            $notif->setIdenchere($enchere->getId());
                            $notif->setMessage("La vente n°".$enchere->getId()." a changé de fournisseur, l'ancien prix de ".$oldprice."€ n'est plus valable, desormais il sera de ".$enchere->getPrix()."€ . Vous pouvez acceder à la vente ");
                            $notif->setEtat("unread");
                            $notif->setCreatedAt($datenotif);
                            $em->persist($notif);
                            $em->flush();
                    }
                }
                $infoFourni = $em->getRepository('AppBundle:User')->findOneById($oldfourni);
                $message = \Swift_Message::newInstance()
                        ->setSubject('CANEO : Changement de prix sur Vente')//objet du mail
                        ->setFrom(array('anton51200@laposte.net' => 'CANEO Website[Do not reply]')) //adresse expéditeur
                        //->setReadReceiptTo('ninon.pelaez@gmail.com') //accusé de réception
                        ->setTo($infoFourni->getEmailCanonical()) //adresse du cabinet qui commande
                        // ->setTo('anton071192@gmail.com') //adresse du cabinet qui commande
                        ->setCharset('utf-8')
                        ->setContentType('text/html')
                        //corps du texte : valeurs à appeler dans la vue mail_cabinet.html.twig
                        ->setBody("La vente n°".$enchere->getId()." a changé de fournisseur, l'ancien prix de ".$oldprice."€ n'est plus valable, desormais il sera de ".$enchere->getPrix()."€ . Vous pouvez acceder à la vente directement sur le site depuis votre onglet Notifications.");
                    $this->get('mailer')->send($message); //action d'envoi

                $request->getSession()
                ->getFlashBag()
                ->add('success', 'Le Nouveau Prix à été enregistré, vous êtes desormais le fournisseur de cette vente !')
                ;

                return $this->redirectToRoute('fiche_enchere', array('idenchere' => $idenchere));
            }

        }

        return $this->render('encheres/enchere_up.html.twig', array(
            'user' => $user,
            'idenchere' => $idenchere,
            'enchere' => $enchere,
        ));
    }

    public function calculAjaxAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $prix = $this->getRequest()->request->get('newprice');

        $com = $prix * 0.2;
        $prixfourni = $prix + $com;

        $tabresult [] = array(
            'com' => $com,
            'prixfourni' => $prixfourni,
        );

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($tabresult, 'json');

        $response = new Response($jsonContent);
        return $response;
    }

    public function sellOverAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tabAcheteurs = [];

        $idenchere = $request->request->get('state');

        $currentdate = new \DateTime();
        $datenotif = $currentdate->format('Y/m/d');

        $result = "alreadyClose";

        $enchere = $em->getRepository('DealBundle:Encheres')->findOneById($idenchere);


        if ($enchere->getEtat() == "open") {
            $enchere->setEtat("close");

            $product = $em->getRepository('ProductBundle:Produit')->findOneById($enchere->getIdproduit());

            if($product->getEtat() == "oui") {
                $product->setEtat("non");
            }

            $em->persist($enchere);
            $em->flush();
            $em->persist($product);
            $em->flush();
            
            $result = "close";

            $fournisseur = $enchere->getIdfournisseur();
            $infoFourni = $em->getRepository('AppBundle:User')->findOneById($enchere->getIdfournisseur());
            $allCmd = $em->getRepository('DealBundle:Commandes')->findByIdenchere($enchere->getId());
            $nbreLivraison = count($em->getRepository('DealBundle:Commandes')->findByIdenchere($enchere->getId()));

            $prixfinal = $enchere->getPrix();
            $com = $prixfinal * 0.2;
            $prixfourni = $prixfinal + $com;

            foreach($allCmd as $oneCmd) {
                $acheteur = $em->getRepository('AppBundle:User')->findOneById($oneCmd->getIdacheteur());

                $montantF = $oneCmd->getNbredecommande() * $prixfourni;
                $montantCom = $oneCmd->getNbredecommande() * $com;

                $tabAcheteurs [] = array(
                    'nom' => $acheteur->getNom(),
                    'commandes' => $oneCmd->getNbredecommande(),
                    'adresseLivr' => $acheteur->getAdresselivraison(),
                    'cpLivr' => $acheteur->getCplivraison(),
                    'villeLivr' => $acheteur->getVillelivraison(),
                    'adresseFactu' => $acheteur->getAdressefactu(),
                    'cpFactu' => $acheteur->getCpfactu(),
                    'villeFactu' => $acheteur->getVillefactu(),
                    'telephone' => $acheteur->getTelephone(),
                    'email' => $acheteur->getEmail(),
                    'montantF' => $montantF,
                    'montantCom' => $montantCom,
                );

            }

            // FOR FOURNISSEUR
            $notiffourni = new Infos();
            $notiffourni->setIduser($fournisseur);
            $notiffourni->setIdenchere($enchere->getId());
            $notiffourni->setMessage("La vente n°".$enchere->getId()." est terminé, le prix unitaire final est de ".$enchere->getPrix()."€. Il y à un total de ".$enchere->getTotalcommande()." pour ".$nbreLivraison." points de livraison. Pour recevoir le détail de la vente, veuillez régler la commission auprès du site.");
            $notiffourni->setEtat("unread");
            $notiffourni->setCreatedAt($datenotif);
            $em->persist($notiffourni);
            $em->flush();


            $messagefourni = \Swift_Message::newInstance()
                ->setSubject('CANEO : Vente terminée')//objet du mail
                ->setFrom(array('anton51200@laposte.net' => 'CANEO Website[Do not reply]')) //adresse expéditeur
                //->setReadReceiptTo('ninon.pelaez@gmail.com') //accusé de réception
                ->setTo($infoFourni->getEmailCanonical()) //adresse du cabinet qui commande
                // ->setTo('anton071192@gmail.com') //adresse du cabinet qui commande
                ->setCharset('utf-8')
                ->setContentType('text/html')
                //corps du texte : valeurs à appeler dans la vue mail_cabinet.html.twig
                ->setBody("La vente n°".$enchere->getId()." est terminé, le prix unitaire final est de ".$enchere->getPrix()."€. Il y à un total de ".$enchere->getTotalcommande()." pour ".$nbreLivraison." points de livraison. Pour recevoir le détail de la vente, veuillez régler la commission auprès du site.");
            $this->get('mailer')->send($messagefourni); //action d'envoi

            // FOR NEO3D
                $messageneo = \Swift_Message::newInstance()
                    ->setSubject('CANEO : Vente terminée')//objet du mail
                    ->setFrom(array('anton51200@laposte.net' => 'CANEO Website[Do not reply]')) //adresse expéditeur
                    //->setReadReceiptTo('ninon.pelaez@gmail.com') //accusé de réception
                    ->setTo('scan@neo3d.fr') //adresse du cabinet qui commande
                    // ->setTo('anton071192@gmail.com') //adresse du cabinet qui commande
                    ->setCharset('utf-8')
                    ->setContentType('text/html')
                    ->setBody($this->renderView('mail/mail_neo3d.html.twig', array(
                        'enchere' => $enchere,
                        'fournisseur' => $infoFourni,
                        'produit' => $product,
                        'nbreLivraison' => $nbreLivraison,
                        'allCmd' => $allCmd,
                        'tabAcheteurs' => $tabAcheteurs,
                        'prixfinal' => $prixfinal,
                        'com' => $com,
                        'prixfourni' => $prixfourni,
                    )));
                $this->get('mailer')->send($messageneo); //action d'envoi

            // FOR ACHETEUR
            foreach ($allCmd as $userAssociate) {
                $notif = new Infos();
                $notif->setIduser($userAssociate->getIdacheteur());
                $notif->setIdenchere($enchere->getId());
                $notif->setMessage("La vente n°".$enchere->getId()." est terminé, le prix unitaire final est de ".$enchere->getPrix()."€. vous avez commandé ".$userAssociate->getNbredecommande()." unité du produit. Vous recevrez par mail les détails concernant la livraison et le fournisseur.");
                $notif->setEtat("unread");
                $notif->setCreatedAt($datenotif);
                $em->persist($notif);
                $em->flush();

                $infoUser = $em->getRepository('AppBundle:User')->findOneById($userAssociate->getIdacheteur());

                $message = \Swift_Message::newInstance()
                    ->setSubject('CANEO : Vente terminée')//objet du mail
                    ->setFrom(array('anton51200@laposte.net' => 'CANEO Website[Do not reply]')) //adresse expéditeur
                    //->setReadReceiptTo('ninon.pelaez@gmail.com') //accusé de réception
                    ->setTo($infoUser->getEmailCanonical()) //adresse du cabinet qui commande
                    // ->setTo('anton071192@gmail.com') //adresse du cabinet qui commande
                    ->setCharset('utf-8')
                    ->setContentType('text/html')
                    //corps du texte : valeurs à appeler dans la vue mail_cabinet.html.twig
                    ->setBody("La vente n°".$enchere->getId()." est terminé, le prix unitaire final est de ".$enchere->getPrix()."€. vous avez commandé ".$userAssociate->getNbredecommande()." unité du produit. Le fournisseur de cette vente est ".$infoFourni->getNom()." vos coordonées lui seront transmise dès que possible et vous serez mis en contact.");
                $this->get('mailer')->send($message); //action d'envoi
            }
        }

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($result, 'json');

        $response = new Response($jsonContent);
        return $response;
    }

    public function newEnchere2Action(Request $request, $idproduct)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $product = $em->getRepository('ProductBundle:Produit')->findOneById($idproduct);

        $cmd = $request->request->get('cmd');

        if($cmd != NULL && $cmd >= $product->getCommandemaximal()) {
            $product->setEtat("oui");

            $em->persist($product);
            $em->flush();

            $datenew = $request->request->get('datenew');
            $dateNew = new \DateTime($datenew);
            $datemid = $request->request->get('datemid');
            $dateMid = new \DateTime($datemid);
            $dateold = $request->request->get('dateold');
            $dateOld = new \DateTime($dateold);
            $fulldate = $request->request->get('fulldate');
            $fulldate = new \DateTime($fulldate);

            $com = $product->getPrixminimal() * 0.2;
            $prixfourni = $product->getPrixminimal() + $com;

            $enchere = new Encheres();
            $enchere->setIdproduit($idproduct);
            $enchere->setIdfournisseur($product->getIdfournisseur());
            $enchere->setPrix($prixfourni);
            $enchere->setCommission($com);
            $enchere->setBeneffourni($product->getPrixminimal());
            $enchere->setTotalcommande($cmd);
            $enchere->setDatenew($dateNew);
            $enchere->setDatemid($dateMid);
            $enchere->setDateold($dateOld);
            $enchere->setFulldate($fulldate);
            $enchere->setEtat("open");
            $enchere->setEtatnew('new');

            $datenotif = $fulldate->format('Y/m/d');

            $em->persist($enchere);
            $em->flush();

            $commande = new Commandes();
            $commande->setIdenchere($enchere->getId());
            $commande->setIdacheteur($user->getId());
            $commande->setNbredecommande($cmd);

            $em->persist($commande);
            $em->flush();

            $favs = $em->getRepository('ProductBundle:Favoris')->findByIdproduit($idproduct);

            foreach($favs as $fav) {
                $associateMember = $em->getRepository('AppBundle:User')->findOneById($fav->getIdacheteur());

                if($associateMember->getType() == "acheteur" && $associateMember->getId() != $user->getId()) {
                    $notif = new Infos();
                    $notif->setIduser($associateMember->getId());
                    $notif->setIdenchere($enchere->getId());
                    $notif->setMessage("Une vente pour le produit : ".$product->getNom()." viens de commencer. Le prix de vente unitaire est actuellement de ".$product->getPrixminimal()." vous pouvez acceder à l'enchère ");
                    $notif->setEtat("unread");
                    $notif->setCreatedAt($datenotif);
                    $em->persist($notif);
                    $em->flush();
                }
                elseif($associateMember->getType() == "fournisseur" && $associateMember->getId() == $product->getIdfournisseur()) {
                    $notif = new Infos();
                    $notif->setIduser($associateMember->getId());
                    $notif->setIdenchere($enchere->getId());
                    $notif->setMessage("Une vente pour le produit : ".$product->getNom()." viens de commencer. Vous êtes le fournisseur actuel de cette vente, et le prix unitaire est actuellement de ".$product->getPrixminimal()."€. Une première commande de ".$cmd."pièces est déjà enregistré. Vous pouvez acceder à la vente ");
                    $notif->setEtat("unread");
                    $notif->setCreatedAt($datenotif);
                    $em->persist($notif);
                    $em->flush();

                    $message = \Swift_Message::newInstance()
                        ->setSubject('CANEO : Nouvelle vente')//objet du mail
                        ->setFrom(array('anton51200@laposte.net' => 'CANEO Website[Do not reply]')) //adresse expéditeur
                        //->setReadReceiptTo('ninon.pelaez@gmail.com') //accusé de réception
                        ->setTo($associateMember->getEmailCanonical()) //adresse du cabinet qui commande
                        // ->setTo('anton071192@gmail.com') //adresse du cabinet qui commande
                        ->setCharset('utf-8')
                        ->setContentType('text/html')
                        //corps du texte : valeurs à appeler dans la vue mail_cabinet.html.twig
                        ->setBody("Une vente pour le produit : ".$product->getNom()." viens de commencer. Vous êtes le fournisseur actuel de cette vente, et le prix unitaire est actuellement de ".$product->getPrixminimal()."€ . Une première commande de ".$cmd."pièces est déjà enregistré. Vous pouvez acceder a la vente sur le site directement depuis votre espace.");
                    $this->get('mailer')->send($message); //action d'envoi
                }
                else {
                    $notif = new Infos();
                    $notif->setIduser($associateMember->getId());
                    $notif->setIdenchere($enchere->getId());
                    $notif->setMessage("Une vente pour le produit : ".$product->getNom()." viens de commencer. Le prix de vente unitaire est actuellement de ".$product->getPrixminimal()."€. Une commande de ".$cmd."unitées est déjà passé");
                    $notif->setEtat("unread");
                    $notif->setCreatedAt($datenotif);
                    $em->persist($notif);
                    $em->flush();
                }
            }

            $request->getSession()
                ->getFlashBag()
                ->add('success', 'Votre commande à été validé, et la vente a démarré.')
                ;
        }

        return $this->render('encheres/new_enchere2.html.twig', array(
            'idproduct' => $idproduct,
            'user' => $user,
            'product' => $product,
        ));
    }
}