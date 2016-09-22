<?php

namespace DealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Encheres
 *
 * @ORM\Table(name="encheres")
 * @ORM\Entity(repositoryClass="DealBundle\Repository\EncheresRepository")
 */
class Encheres
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idproduit", type="integer", nullable=true)
     */
    private $idproduit;

    /**
     * @var int
     *
     * @ORM\Column(name="idfournisseur", type="integer", nullable=true)
     */
    private $idfournisseur;

    /**
     * @var float
     *
     * @ORM\Column(name="prix", type="float", nullable=true)
     */
    private $prix;

    /**
     * @var int
     *
     * @ORM\Column(name="totalcommande", type="integer", nullable=true)
     */
    private $totalcommande;

    /**
     * @var int
     *
     * @ORM\Column(name="moisFin", type="integer", nullable=true)
     */
    private $moisFin;

    /**
     * @var int
     *
     * @ORM\Column(name="jourFin", type="integer", nullable=true)
     */
    private $jourFin;

    /**
     * @var int
     *
     * @ORM\Column(name="anneeFin", type="integer", nullable=true)
     */
    private $anneeFin;

    /**
     * @var date
     *
     * @ORM\Column(name="fulldate", type="date", nullable=true)
     */
    private $fulldate;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set idproduit
     *
     * @param integer $idproduit
     * @return Encheres
     */
    public function setIdproduit($idproduit)
    {
        $this->idproduit = $idproduit;

        return $this;
    }

    /**
     * Get idproduit
     *
     * @return integer 
     */
    public function getIdproduit()
    {
        return $this->idproduit;
    }

    /**
     * Set idfournisseur
     *
     * @param integer $idfournisseur
     * @return Encheres
     */
    public function setIdfournisseur($idfournisseur)
    {
        $this->idfournisseur = $idfournisseur;

        return $this;
    }

    /**
     * Get idfournisseur
     *
     * @return integer 
     */
    public function getIdfournisseur()
    {
        return $this->idfournisseur;
    }

    /**
     * Set prix
     *
     * @param float $prix
     * @return Encheres
     */
    public function setPrix($prix)
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * Get prix
     *
     * @return float 
     */
    public function getPrix()
    {
        return $this->prix;
    }

    /**
     * Set totalcommande
     *
     * @param integer $totalcommande
     * @return Encheres
     */
    public function setTotalcommande($totalcommande)
    {
        $this->totalcommande = $totalcommande;

        return $this;
    }

    /**
     * Get totalcommande
     *
     * @return integer 
     */
    public function getTotalcommande()
    {
        return $this->totalcommande;
    }

    /**
     * Set moisFin
     *
     * @param integer $moisFin
     * @return Encheres
     */
    public function setMoisFin($moisFin)
    {
        $this->moisFin = $moisFin;

        return $this;
    }

    /**
     * Get moisFin
     *
     * @return integer 
     */
    public function getMoisFin()
    {
        return $this->moisFin;
    }

    /**
     * Set jourFin
     *
     * @param integer $jourFin
     * @return Encheres
     */
    public function setJourFin($jourFin)
    {
        $this->jourFin = $jourFin;

        return $this;
    }

    /**
     * Get jourFin
     *
     * @return integer 
     */
    public function getJourFin()
    {
        return $this->jourFin;
    }

    /**
     * Set anneeFin
     *
     * @param integer $anneeFin
     * @return Encheres
     */
    public function setAnneeFin($anneeFin)
    {
        $this->anneeFin = $anneeFin;

        return $this;
    }

    /**
     * Get anneeFin
     *
     * @return integer 
     */
    public function getAnneeFin()
    {
        return $this->anneeFin;
    }

    /**
     * Set fulldate
     *
     * @param \DateTime $fulldate
     * @return Encheres
     */
    public function setFulldate($fulldate)
    {
        $this->fulldate = $fulldate;

        return $this;
    }

    /**
     * Get fulldate
     *
     * @return \DateTime 
     */
    public function getFulldate()
    {
        return $this->fulldate;
    }
}
