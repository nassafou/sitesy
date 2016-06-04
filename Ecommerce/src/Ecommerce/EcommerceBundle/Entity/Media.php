<?php

namespace Ecommerce\EcommerceBundle\Entity;

use Doctrine\Common\DataFixtures\ReferenceRepository;

use Doctrine\ORM\Mapping as ORM;
//use Symfony\Component\validator\constraints as Assert;

/**
 * Media
 *
 * @ORM\Table("media")
 * @ORM\Entity(repositoryClass="Ecommerce\EcommerceBundle\Repository\MediaRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Media
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     *@var \DateTime
     *
     *@ORM\Column(name="update_at", type="datetime", nullable=true)
     */
    private $updateAt;//met a jour l'entité updateAt
    
    /**
     *@ORM\PostLoad()
     */
    public function postLoad()
    {
     $this->updateAt = new \DateTime(); // initialisation de updateAt
    }
    
    /**
     *@ORM\Column(type="string", length=255)
     */
    public $name; // nom du lien
    
    /**
     *@ORM\Column(type="string", length=255, nullable=true)
     */
    public $path; // stock le nom de l'image
    
    public $file; // attribut pour le champ file du formulaire
    
    public function getUploadRootDir() 
    {
         return __dir__.'/../../../../web/uploads'; //le chemin du dossier de stockage ou destination du fichier
    }
    
    public function getAbsolutePath() 
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;// nom du fichier concaténé a l'extention
    }
   
    public function getAssetPath()
    {
     return 'uploads/'.$this->path; // méthode qui retourne le nom du chemin pour la produits et presentation ou recuperer la route
    }
    
    /**
     *@ORM\PrePersist()
     *@ORM\PreUpdate()
     */
    public function preUpload()
    {
        $this->tempFile = $this->getAbsolutePath(); // fichier temp lorsqu'on fait un upload
        $this->oldFile = $this->getPath(); // quand on modifie l'image on peut recupérer l'anncien
        $this->updateAt =  new \DateTime();
        
        if(null !== $this->file) // vérification si le file n'est pas null 
        $this->path = sha1(uniqid(mt_rand(), true)).'.'.$this->file->guessExtension(); // le hache avec sha1  en le renomant et on concatene avec l'extension 
    }
    
    /**
     *@ORM\PostPersist()
     *@ORM\PostUpdate()
     */
    public function upload()
    {
        if(null !== $this->file) // vérifie si le file n'est pas vide
        {
            $this->file->move($this->getUploadRootDir(), $this->path);
            unset($this->file); // supprimer le file temporaire
            
            if ($this->oldFile != null)  unlink($this->tempFile); // si l'ancien fichier existe on le supprime
        }
    }
    
    /**
     *@ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        $this->tempFile = $this->getAbsolutePath();
    }
    
    /**
     *@ORM\PostRemove()
     *
     */
    public function removeUpload()
    {
         if(file_exists($this->tempFile)) unlink($this->tempFile);// si le fichier existe on le supprime
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getName()
    {
        return $this->name;
    }
}