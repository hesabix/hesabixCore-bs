<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\Commodity;
use App\Entity\CommodityCat;
use App\Entity\CommodityDrop;
use App\Entity\CommodityUnit;
use App\Service\Access;
use App\Service\Jdate;
use App\Service\Log;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommodityController extends AbstractController
{
    #[Route('/api/commodity/list', name: 'app_commodity_list')]
    public function app_commodity_list(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$access->hasRole('commodity'))
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(array_key_exists('speedAccess',$params)){
            $items = $entityManager->getRepository(Commodity::class)->findBy([
                'bid'=>$request->headers->get('activeBid'),
                'speedAccess'=>true
            ]);
        }
        else{
            $items = $entityManager->getRepository(Commodity::class)->findBy([
                'bid'=>$request->headers->get('activeBid')
            ]);
        }
        $res = [];
        foreach ($items as $item){
            $temp = [];
            $temp['id'] = $item->getId();
            $temp['name'] = $item->getName();
            $temp['unit'] = $item->getUnit()->getName();
            $temp['des'] = $item->getDes();
            $temp['priceBuy'] = $item->getPriceBuy();
            $temp['speedAccess'] = $item->isSpeedAccess();
            $temp['priceSell'] = $item->getPriceSell();
            $temp['code'] = $item->getCode();
            $temp['cat'] = null;
            if($item->getCat())
                $temp['cat'] = $item->getCat()->getName();
            $temp['khadamat'] = false;
            if($item->isKhadamat())
                $temp['khadamat'] = true;

            $temp['commodityCountCheck'] = $item->isCommodityCountCheck();
            $temp['minOrderCount'] = $item->getMinOrderCount();
            $temp['dayLoading'] = $item->getDayLoading();
            $temp['orderPoint'] = $item->getOrderPoint();
            $res[] = $temp;
        }
        return $this->json($res);
    }
    #[Route('/api/commodity/list/print', name: 'app_commodity_list_print')]
    public function app_commodity_list_print(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(Commodity::class)->findBy([
            'bid'=>$request->headers->get('activeBid')
        ]);
        $pid = $provider->createPrint(
            $acc['bid'],
            $this->getUser(),
            $this->renderView('pdf/commodity.html.twig',[
                'page_title'=>'فهرست کالا و خدمات',
                'bid'=>$acc['bid'],
                'persons'=>$items
            ]));
        return $this->json(['id'=>$pid]);
    }
    #[Route('/api/commodity/info/{code}', name: 'app_commodity_info')]
    public function app_commodity_info($code,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(Commodity::class)->findOneBy([
            'bid'=>$acc['bid'],
            'code'=>$code
        ]);
        $data->setUnit($data->getUnit()->getName());
        $res = $provider->Entity2ArrayJustIncludes($data,['isSpeedAccess','isCommodityCountCheck','getName','getUnit','getPriceBuy','getPriceSell','getCat','getOrderPoint','getdes','getId','getDayLoading','isKhadamat','getCode','getMinOrderCount','getLabel'],1);
        $res['cat'] = '';
        if($data->getCat())
            $res['cat'] = $data->getCat()->getId();
        return $this->json($res);
    }

    #[Route('/api/commodity/mod/{code}', name: 'app_commodity_mod')]
    public function app_commodity_mod(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('name',$params))
            return $this->json(['result'=>-1]);
        if(count_chars(trim($params['name'])) == 0)
            return $this->json(['result'=>3]);
        if($code == 0){
            $data = $entityManager->getRepository(Commodity::class)->findOneBy([
                'name'=>$params['name']
            ]);
            //check exist before
            if($data)
                return $this->json(['result'=>2]);
            $data = new Commodity();
            $data->setCode($provider->getAccountingCode($request->headers->get('activeBid'),'Commodity'));
        }
        else{
            $data = $entityManager->getRepository(Commodity::class)->findOneBy([
                'bid'=>$acc['bid'],
                'code'=>$code
            ]);
            if(!$data)
                throw $this->createNotFoundException();
        }
        $unit = $entityManager->getRepository(CommodityUnit::class)->findOneBy(['name'=>$params['unit']]);
        if(!$unit)
            throw $this->createNotFoundException('unit not fount!');
        $data->setUnit($unit);
        $data->setBid($acc['bid']);
        $data->setname($params['name']);
        if($params['khadamat'] == 'true') $data->setKhadamat(true);
        else $data->setKhadamat(false);
        $data->setDes($params['des']);
        $data->setPriceSell($params['priceSell']);
        $data->setPriceBuy($params['priceBuy']);
        if(array_key_exists('commodityCountCheck',$params)){
            $data->setCommodityCountCheck($params['commodityCountCheck']);
        }
        $data->setMinOrderCount($params['minOrderCount']);
        $data->setSpeedAccess($params['speedAccess']);
        $data->setDayLoading($params['dayLoading']);
        $data->setOrderPoint($params['orderPoint']);
        //set cat
        if(array_key_exists('cat',$params)){
            if($params['cat'] != ''){
                if(is_int($params['cat']))
                    $cat = $entityManager->getRepository(CommodityCat::class)->find($params['cat']);
                else
                    $cat = $entityManager->getRepository(CommodityCat::class)->find($params['cat']['id']);
                if($cat){
                    if($cat->getBid() == $acc['bid']){
                        $data->setCat($cat);
                    }
                }
            }
        }
        $entityManager->persist($data);
        $entityManager->flush();
        $log->insert('کالا و خدمات','کالا / خدمات با نام  ' . $params['name'] . ' افزوده/ویرایش شد.',$this->getUser(),$request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }

    #[Route('/api/commodity/units', name: 'app_commodity_units')]
    public function app_commodity_units(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$access->hasRole('commodity'))
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(CommodityUnit::class)->findAll();
        return $this->json($items);
    }

    #[Route('/api/commodity/drop/list', name: 'app_commodity_drop_list')]
    public function app_commodity_drop_list(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$access->hasRole('commodity'))
            throw $this->createAccessDeniedException();
        $items = $entityManager->getRepository(CommodityDrop::class)->findBy([
            'bid'=>$request->headers->get('activeBid')
        ]);
        $generalItems = $entityManager->getRepository(CommodityDrop::class)->findBy([
            'bid'=>null
        ]);

        return $this->json($provider->ArrayEntity2Array(array_merge($items,$generalItems),0));
    }

    #[Route('/api/commodity/drop/mod/{code}', name: 'app_commodity_drop_mod')]
    public function app_commodity_drop_mod(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('name',$params))
            return $this->json(['result'=>-1]);
        if(count_chars(trim($params['name'])) == 0)
            return $this->json(['result'=>3]);
        if($code == 0){
            $data = $entityManager->getRepository(CommodityDrop::class)->findOneBy([
                'name'=>$params['name'],
                'bid'=>$acc['bid']
            ]);
            //check exist before
            if($data)
                return $this->json(['result'=>2]);
            $data = new CommodityDrop();
        }
        else{
            $data = $entityManager->getRepository(CommodityDrop::class)->findOneBy([
                'bid'=>$acc['bid'],
                'id'=>$code
            ]);
            if(!$data)
                throw $this->createNotFoundException();
        }
        $data->setName($params['name']);
        $data->setBid($acc['bid']);
        $data->setCanEdit(true);
        $entityManager->persist($data);
        $entityManager->flush();
        $log->insert('کالا و خدمات','ویژگی کالا / خدمات با نام ' . $params['name'] . ' افزوده/ویرایش شد.',$this->getUser(),$request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }

    /**
     * @throws \ReflectionException
     */
    #[Route('/api/commodity/drop/info/{code}', name: 'app_commodity_drop_info')]
    public function app_commodity_drop_info($code,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $data = $entityManager->getRepository(CommodityDrop::class)->findOneBy([
            'bid'=>$acc['bid'],
            'id'=>$code
        ]);
        return $this->json($provider->Entity2Array($data,0));
    }
    #[Route('/api/commodity/cat/get/line', name: 'app_commodity_cat_get_line')]
    public function app_commodity_cat_get_line(Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {

        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $temp =[];
        $nodes = $entityManager->getRepository(CommodityCat::class)->findBy([
            'bid'=>$acc['bid'],
        ]);
        if(count($nodes) == 0)
            $nodes = $this->createDefaultCat($acc['bid'],$entityManager);
        return $this->json($provider->ArrayEntity2Array($nodes,0));
    }

    #[Route('/api/commodity/cat/get', name: 'app_commodity_cat_get')]
    public function app_commodity_cat_get(Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {

        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $temp =[];
        $nodes = $entityManager->getRepository(CommodityCat::class)->findBy([
            'bid'=>$acc['bid']
        ]);
        if(count($nodes) == 0)
            $nodes = $this->createDefaultCat($acc['bid'],$entityManager);
        foreach ($nodes as $node){
            if($this->hasChild($entityManager,$node)){
                $temp[$node->getId()]=[
                    'text'=>$node->getName(),
                    'children'=>$this->getChildsLabel($entityManager,$node)
                ];
            }
            else{
                $temp[$node->getId()]=[
                    'text'=>$node->getName(),
                ];
            }
        }
        $root = $entityManager->getRepository(CommodityCat::class)->findOneBy([
            'bid'=>$acc['bid'],
            'root'=>true
        ]);
        return $this->json(['items'=>$temp,'root'=>$root->getId()]);
    }

    #[Route('/api/commodity/cat/childs', name: 'app_commodity_cat_childs')]
    public function app_commodity_cat_childs(Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();

        $items= $entityManager->getRepository(CommodityCat::class)->findOneBy([
            'bid'=>$acc['bid'],
            'root'=>true
        ]);
        return $this->json($this->getChilds($entityManager,$items));
    }
    #[Route('/api/commodity/cat/insert', name: 'app_commodity_cat_insert')]
    public function app_commodity_cat_insert(Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('upper',$params) || !array_key_exists('text',$params))
            return $this->json(['result'=>-1]);
        $upper = $entityManager->getRepository(CommodityCat::class)->find($params['upper']);
        if($upper){
            if($upper->getBid() == $acc['bid']){
                $cat = new CommodityCat();
                $cat->setBid($acc['bid']);
                $cat->setRoot(false);
                $cat->setName($params['text']);
                $cat->setUpper($upper->getId());
                $entityManager->persist($cat);
                $entityManager->flush();
                return $this->json(['result'=>1,'id'=>$cat->getId()]);
            }
        }
        return $this->json(['result'=>1]);
    }
    #[Route('/api/commodity/cat/edit', name: 'app_commodity_cat_edit')]
    public function app_commodity_cat_edit(Jdate $jdate,Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(!array_key_exists('id',$params) || !array_key_exists('text',$params))
            return $this->json(['result'=>-1]);
        $node = $entityManager->getRepository(CommodityCat::class)->find($params['id']);
        if($node){
            if($node->getBid() == $acc['bid']){
                $node->setName($params['text']);
                $entityManager->persist($node);
                $entityManager->flush();
                return $this->json(['result'=>1,'id'=>$node->getId()]);
            }
        }
        return $this->json(['result'=>1]);
    }
    private function getChildsLabel(EntityManagerInterface $entityManager, mixed $node){
        $childs =  $entityManager->getRepository(CommodityCat::class)->findBy([
            'upper'=>$node
        ]);
        $temp = [];
        foreach ($childs as $child){
            $temp[] = $child->getId();
        }
        return $temp;
    }

    private function hasChild(EntityManagerInterface $entityManager, mixed $node)
    {
        if(count($entityManager->getRepository(CommodityCat::class)->findBy([
                'upper'=>$node
            ]))!= 0)
            return true;
        return false;
    }

    private function getChilds(EntityManagerInterface $entityManager, mixed $node){
        $childs =  $entityManager->getRepository(CommodityCat::class)->findBy([
            'upper'=>$node
        ]);
        $temp = [];
        foreach ($childs as $child){
            if($this->hasChild($entityManager,$child)){
                $temp[]=[
                    'id'=>$child->getId(),
                    'label'=>$child->getName(),
                    'children'=>$this->getChilds($entityManager,$child)
                ];
            }
            else{
                $temp[]=[
                    'id'=>$child->getId(),
                    'label'=>$child->getName(),
                ];
            }
        }
        return $temp;
    }

    public function createDefaultCat(Business $bid,EntityManagerInterface $en): array
    {
        $item = new CommodityCat();
        $item->setName('دسته بندی ها');
        $item->setUpper(null);
        $item->setBid($bid);
        $item->setRoot(true);
        $en->persist($item);
        $en->flush();

        $child = new CommodityCat();
        $child->setUpper($item->getId());
        $child->setBid($bid);
        $child->setName('بدون دسته‌بندی');
        $en->persist($child);
        $en->flush();
        return [$item,$child];
    }

    #[Route('/api/commodity/import/excel', name: 'app_commodity_import_excel')]
    public function app_commodity_import_excel(Provider $provider,Request $request,Access $access,Log $log,EntityManagerInterface $entityManager,$code = 0): JsonResponse
    {
        $acc = $access->hasRole('commodity');
        if(!$acc)
            throw $this->createAccessDeniedException();
        $file = $request->files->get('file');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $data = $sheet->toArray();
        unset($data[0]);
        foreach($data as $item){
            //load cat
            $unit = $entityManager->getRepository(commodity::class)->findOneBy([
                'name'=>$item[7],
            ]);
            if(!$unit){
                $unit = $entityManager->getRepository(CommodityUnit::class)->findAll()[0];
            }

            $commodity = $entityManager->getRepository(commodity::class)->findOneBy([
                'name'=>$item[2],
                'bid' =>$acc['bid']
            ]);
            //check exist before
            if(!$commodity){
                $commodity = new commodity();
                $commodity->setCode($provider->getAccountingCode($request->headers->get('activeBid'),'commodity'));
                $commodity->setName($item[2]);
                $commodity->setBid($acc['bid']);
                $commodity->setUnit($unit);
                $commodity->setOrderPoint(0);
                $commodity->setDayLoading(0);
                if(array_key_exists(1,$item))
                    $commodity->setName($item[1]);
                if(array_key_exists(3,$item))
                    $commodity->setPriceSell($item[3]);
                if(array_key_exists(4,$item))
                    $commodity->setPriceBuy($item[4]);
                if(array_key_exists(1,$item))
                    $commodity->setSpeedAccess($item[1]);
                if(array_key_exists(5,$item))
                    $commodity->setMinOrderCount($item[5]);
                if(array_key_exists(6,$item))
                    $commodity->setDes($item[6]);
                if(array_key_exists(0,$item)){
                    $commodity->setKhadamat(true);
                    if($item[0] == '1'){
                        $commodity->setKhadamat(false);
                    }
                }
                $entityManager->persist($commodity);
            }
           $entityManager->flush();
        }
        $log->insert('کالا/خدمات','تعداد '. count($data) . ' کالا یا خدمات به صورت گروهی وارد شد.',$this->getUser(),$request->headers->get('activeBid'));
        return $this->json(['result' => 1]);
    }
}

