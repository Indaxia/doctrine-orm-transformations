<?php

namespace ScorpioT1000\OTR\Demo\Symfony;

use \Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController;
use \Symfony\Component\Serializer\Encoder\JsonEncoder;

use \ScorpioT1000\OTR\ITransformable;
use \ScorpioT1000\OTR\Traits\Transformable;
use \ScorpioT1000\OTR\Annotations\Policy;
use \ScorpioT1000\OTR\Annotations\PolicyResolver;
use \ScorpioT1000\OTR\Annotations\PolicyResolverProfiler;

use \ScorpioT1000\OTR\Demo\Entity\THead;
use \ScorpioT1000\OTR\Demo\Entity\TSub;
use \ScorpioT1000\OTR\Demo\Entity\TSubCol;
use \ScorpioT1000\OTR\Demo\Entity\WithPolicy;

class Controller extends SymfonyController
{
    /**
     * @Route("/to-array")
     */
    public function toArrayAction() {
        try {
            $th = new THead();
            for($i=0; $i<5; ++$i) { $th->getMany2many()->add(new TSubCol()); }
            $ts = new TSub();
            for($i=0; $i<2; ++$i) { $ts->getOne2many()->add(new TSubCol()); }
            $th->setMany2one($ts);
            $th->setOne2one(new TSub());
            
            $this->getEM()->persist($th);
            $this->getEM()->flush();
            $pr = new PolicyResolverProfiler();
            $arr = $th->toArray(null, null, $pr);
            
            return $this->success(['result' => $arr, 'profiler' => $pr->results]);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage(), $e->getTraceAsString());
        }
    }
    
    /**
     * @Route("/from-array")
     */
    public function fromArrayAction() {
        try {
            $data = $this->getRequestContentJson();

            if(empty($data)) { return $this->fail('Input must be a JSON object representing Transformable Entity'); }
            $th = empty($data['id'])
                ? null
                : $this->getRepository('THead')->findOneBy(['id' => $data['id']]);
            if(! $th) {
                $th = new THead();
            }
        
            $pr = new PolicyResolverProfiler();
            $th->fromArray($data, $this->getEM(), null, null, $pr);
            $this->getEM()->persist($th);
            $this->getEM()->flush();
            $arr = $th->toArray();
            
            return $this->success(['result' => $arr, 'profiler' => $pr->results]);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage(), $e->getTraceAsString());
        }
        
    }
    
    /**
     * @Route("/to-array-global-policy")
     */
    public function toArrayGlobalPolicyAction() {
        try {
            $wp = new WithPolicy();
            $this->getEM()->persist($wp);
            $this->getEM()->flush();
            $pr = new PolicyResolverProfiler();
            $arr = $wp->toArray(null, null, $pr);
            
            return $this->success(['result' => $arr, 'profiler' => $pr->results]);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage(), $e->getTraceAsString());
        }
    }
    
    
    // ======================= Utils ======================= 
    
    /** @param array $data is optional
	 * @param array|null additional data
      * @return JsonResponse */
    public function success($data = array()) {
        return new JsonResponse(['success' => true, 'data' => $data]);
    }
    
    public function fail($error, $info = []) {
        return new JsonResponse(['success' => false, 'message' => $error, 'info' => $info]);
    }
    
    /** @return \Doctrine\ORM\EntityManager */
    public function getEM() {
        return $this->get('doctrine.orm.entity_manager');
    }
    
    /** Returns request content (in json) as assoc array
      * @return array */
    public function getRequestContentJson() {
        $enc = new JsonEncoder();
        return $enc->decode($this->container->get('request_stack')->getCurrentRequest()->getContent(), 'json');
    }
    
    /** @return \Doctrine\ORM\EntityRepository */
    public function getRepository($name, $ns = "ScorpioT1000\\OTR\\Demo\\Entity") {
        return $this->getEM()->getRepository($ns."\\".$name);
    }
}