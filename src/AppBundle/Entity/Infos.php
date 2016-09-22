<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Infos
 *
 * @ORM\Table(name="infos")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InfosRepository")
 */
class Infos
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
     * @ORM\Column(name="iduser", type="integer", nullable=true)
     */
    private $iduser;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var int
     *
     * @ORM\Column(name="idenchere", type="integer", nullable=true)
     */
    private $idenchere;


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
     * Set iduser
     *
     * @param integer $iduser
     * @return Infos
     */
    public function setIduser($iduser)
    {
        $this->iduser = $iduser;

        return $this;
    }

    /**
     * Get iduser
     *
     * @return integer 
     */
    public function getIduser()
    {
        return $this->iduser;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Infos
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set idenchere
     *
     * @param integer $idenchere
     * @return Infos
     */
    public function setIdenchere($idenchere)
    {
        $this->idenchere = $idenchere;

        return $this;
    }

    /**
     * Get idenchere
     *
     * @return integer 
     */
    public function getIdenchere()
    {
        return $this->idenchere;
    }
}
