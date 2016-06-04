<?php

namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Ecommerce\EcommerceBundle\Controller\ProduitsController;
use Ecommerce\EcommerceBundle\Entity\Produits;
use Ecommerce\EcommerceBundle\Entity\Commandes;
use Ecommerce\EcommerceBundle\Form\UtilisateursAdressesType;
use Ecommerce\EcommerceBundle\Entity\UtilisateursAdresses;

class PanierController extends Controller
{
    
    public function menuAction()
    {
        $session       = $this->getRequest()->getSession();
        if(!$session->has('panier'))
        $articles      = 0 ;
        else
        $articles      = count($session->get('panier'));
        
        return $this->render('EcommerceBundle:Default:panier/modulesUsed/panier.html.twig', array('articles' => $articles));
    }
    
    
    
    public function supprimerAction($id)
    {
        $session      = $this->getRequest()->getSession();
        $panier       = $session->get('panier');
        
        if(array_key_exists($id, $panier))
        {
            unset($panier[$id]);
            $session->set('panier', $panier );
            
           $this->get('session')->getFlashBag()->add('success','Article supprimer avec succès'); 
        }
        return $this->redirect($this->generateUrl('panier')); 
        
    }
    
    public function ajouterAction($id)
    {
        // initialisation d'une variable session
        $session        = $this->getRequest()->getSession();
        
        //vérification de l'existense du produit
        
        if(!$session->has('panier')) $session->set('panier', array());
        $panier         = $session->get('panier');
        
        //$panier [ID PRODUIT] => QUANTITE
        
        if(array_key_exists($id, $panier)){
            if($this->getrequest()->query->get('qte') != null) $panier[$id] = $this->getRequest()->query->get('qte');
            $this->get('session')->getFlashBag()->add('success','la quantité modifiée  avec succès');
        }else {
            if ($this->getRequest()->query->get('qte') !=null)
            $panier['id'] = $this->getRequest()->query->get('qte');
            else
            $panier[$id]= 1;
            $this->get('session')->getFlashBag()->add('success','Article ajouté avec succès');
        }
        
        $session->set('panier', $panier);
        
        
        
        // rediction vers l'action panier du controleur panier
        return $this->redirect($this->generateUrl('panier'));
       
        
    }
    
    public function panierAction()
    {
        // initialisation d'un objet session
        
        $session        = $this->getRequest()->getSession();
        //$session->remove('panier');
        //die();
        //condition si le tableau est different de session faire 
        if(!$session->has('panier')) $session->set('panier', array());
        
        //var_dump($session->get('panier'));
        //die();
        
        //qb permet de recuperer les élements du tableau session pour afficher
        // uniquement les éléments dont ont a besoin 
        // s'il ya des éléments
        $em         = $this->getDoctrine()->getManager();
        $produits   = $em->getRepository('EcommerceBundle:Produits')->findArray(array_keys($session->get('panier')));
        
        
        return $this->render('EcommerceBundle:Default:panier/layout/panier.html.twig', array('produits' => $produits,
                                                                                             'panier' => $session->get('panier')));
    }
    
    
    public function adresseSuppresion($id)
    {
        $em       =$this->getDoctrine()->getManager();
        //créer une entité par rapport a l'utilisateur 
        $entity   = $em->getRepository('EcommerceBundle:UtilisateursAdresses')->findby($id);
        
        // condition de validité
        if($this->container->get('security-context')->getToken()->getUser() != $entity->getUtilisateur() || !$entity )
        
            return $this->redirect ($this->generateUrl('livraison'))  ;
        
            $em->remove($entity);
            $em->flush();
        
       return $this->redirect ($this->generateUrl('livraison'))  ; 
        
    }
    
    
   public function livraisonAction()
    {
        $utilisateur        = $this->container->get('security.context')->getToken()->getUser();
        $entity             = new UtilisateursAdresses();
        $form               = $this->createForm(new UtilisateursAdressesType(), $entity );
        
        //recupération de l'adresse d'utilisateur
        if($this->get('request')->getMethod() == 'POST' )
        {
            $form->handleRequest($this->getRequest());
            if($form->isValid())
              {
                //$utilisateur = $this->container->get('security.context')->getToken()->getUser();
                $em = $this->getDoctrine()->getManager();
                $entity->setUtilisateur($utilisateur);
                $em->persist($entity);
                $em->flush();
                
                return $this->redirect($this->generateUrl('livraison'));
                }            
        }
        return $this->render('EcommerceBundle:Default:panier/layout/livraison.html.twig' ,array( 'utilisateur' => $utilisateur,
                                                                                                 'form' => $form->createView()));
    }
    
    public function setLivraisonOnSession()
    {
            $session                   =  $this->getRequest()->getSession();
        if(!$session->has('adresse'))     $session->set('adresse', array());
            $adresse                   = $session->get('adresse');
        
        if($this->getRequest()->request->get('livraison') != null &&  $this->getRequest()->request->get('facturation') != null)
        {
            $adresse['livraison']   = $this->getRequest()->request->get('livraison');
            $adresse['facturation'] = $this->getRequest()->request->get('facturation');
        }
        else
        {
            return $this->redirect($this->generateUrl('validation'));
        }
        
        $session->set('adresse', $adresse);
        return $this->redirect($this->generateUrl('validation'));
    }
    
    public function validationAction()
    {
        if($this->get('request')->getMethod() == 'POST')
        
            // Appel de la methode setLivraisonOnSession
            $this->setLivraisonOnSession();
            
            $em              = $this->getDoctrine()->getManager();
            $prepareCommande = $this->forward('EcommerceBundle:Commandes:prepareCommande');
            $commande        = $em->getRepository('EcommerceBundle:Commandes')->find($prepareCommande->getContent());
        
            ///ancien code qui marche
            /*
            $em = $this->getDoctrine()->getManager();
            $session = $this->getRequest()->getSession();
            $adresse = $session->get('adresse');
            // recueil des produits validés et de la facturation
            $produits = $em->getRepository('EcommerceBundle:Produits')->findArray(array_keys($session->get('panier')));
            $livraison = $em->getRepository('EcommerceBundle:UtilisateursAdresses')->find($adresse['livraison']);
            $facturation = $em->getRepository('EcommerceBundle:UtilisateursAdresses')->find($adresse['facturation']);
            
             //var_dump($adresse);
            //die();
        return $this->render('EcommerceBundle:Default:panier/layout/validation.html.twig', array('produits' => $produits,
                                                                                                 'livraison' => $livraison,
                                                                                                 'facturation' => $facturation,
    
                                                                                                 'panier' => $session->get('panier')));
                                                                                                 */
            
            //var_dump($commande);
            //die();
            
            return $this->render('EcommerceBundle:Default:panier/layout/validation.html.twig', array('commande' => $commande));
            
    }
    
    
}
