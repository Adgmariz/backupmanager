<?php
  namespace App\Controller;

  use Symfony\Bundle\FrameworkBundle\Controller\Controller;
  use Symfony\Component\Routing\Annotation\Route;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\Form\Extension\Core\Type\TextType;
  use Symfony\Component\Form\Extension\Core\Type\PasswordType;
  use Symfony\Component\HttpFoundation\Session\Session;
  
  use App\Entity\LogTarefa;
  use App\Entity\Tarefa;
  use App\Entity\Agendamento;
  use App\Entity\Usuario;

  class FrontController extends Controller{

      private function prepareFormLogin(){
        return $this->createFormBuilder(null, array(
            'action' => '/login',
            'method' => 'POST',
        ))
        ->add('usuario', TextType::class, array('attr' => [
                    'placeholder' => 'usuario'
        ]))
        ->add('senha', PasswordType::class, array('attr' => [
                    'placeholder' => 'senha'
        ]))->getForm();
      }

      /**
        * @Route("/")
        */
      public function index(){
        //TODO: if/else login para verificar sessão
        $form = $this->prepareFormLogin();
        return $this->render('front/login.html.twig', [
            'form' => $form->createView()
        ]);
      }

      /**
        * @Route("/dashboard")
        */
      public function dashboard(){
        // $session = new Session();
        // return $session->get('islogged') ? 
        return $this->render('front/index.html.twig');
      }

      /**
        * @Route("/login")
        */
      public function login(Request $request){
        $form = $this->prepareFormLogin();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $usuario = $data['usuario'];
            $senha = md5($data['senha']);
            $result = $this->getDoctrine()
             ->getRepository(Usuario::class)
             ->checkUsuario($usuario, $senha);
            $session = new Session();
            $session->set('islogged', true);
             return $this->redirect($result ? 'dashboard' : '/');
             
        }
        else{
          return $this->redirect('/');
        }
      }
      /**
        * @Route("/logout")
        */
      public function logout(){
        return $this->redirect('/');
      }

      /**
       * @Route("/testedb")
       */
      public function testedb(){
        $result = $this->getDoctrine()->getRepository(Agendamento::class)->findAll();
        $tostring = '';
        foreach($result as $res){
          //var_dump($res->getTarefa());exit;
          $tostring.=$res->getTarefa();
        }

        return new Response($tostring);
        //var_dump($result);exit;
        //->getRepository(LogTarefa::class)
      }

      private function prepararAgendamento($agendamento){
        $usuarioToSet = $this->getDoctrine()->getRepository(Usuario::class)->findById($agendamento->getUsuario());
        $agendamento->setUsuario($usuarioToSet[0]);//$usuarioToSet[0] é uma instância de Usuario
        $tarefaToSet = $this->getDoctrine()->getRepository(Tarefa::class)->findById($agendamento->getTarefa());
        $agendamento->setTarefa($tarefaToSet[0]);
        return $agendamento;
      }


      /**
       * @Route("/listaragendamentos")
       */
      public function listaragendamentos(){
        $agendamentos = $this->getDoctrine()->getRepository(Agendamento::class)->findAll();
        foreach($agendamentos as $agendamento){
          $agendamento = $this->prepararAgendamento($agendamento);
        }
        return $this->render('front/listaragendamentos.html.twig', ['agendamentos'=>$agendamentos]);
      }

      /**
       * @Route("/editaragendamentos/{id}")
       */
      public function editaragendamento(Agendamento $agendamento = null){
        try{
          if($agendamento != null){
            $agendamento = $this->prepararAgendamento($agendamento);
            $usuarios = $this->getDoctrine()->getRepository(Usuario::class)->findAll();
            $tarefas = $this->getDoctrine()->getRepository(Tarefa::class)->findAll();
            //TODO: criar uma página de edição. tag <SELECT>(html) para 'Usuario' e 'Tarefa'(de acordo com o id)
            //TODO:(alterar a tarefa que o agendamento está vinculado. Campos de edição(texto) para descrição, frequencia, 
            return $this->render('front/editaragendamentos.html.twig', ['agendamento'=>$agendamento,
                                                                        'usuarios'   =>$usuarios,
                                                                        'tarefas'    =>$tarefas]);
          }
          else{
            throw new \Exception("Objeto 'Agendamento' não encontrado" );
          }
          
        }
        catch(Exception $ex){
          return $ex->getTraceAsString();
        }
      }
      
      /**
       * @Route("/processaeditagend")
       */
      public function processaEditarAgendamento(){
        var_dump($_GET, $_POST);exit;
        return new Response(print_r($_POST));

      }
     /* public function processaEditarAgendamento(){
          $id = $_POST[]
          $agendamento = $this->getDoctrine()->getRepository(Agendamento::class)->find($id);
          
        return $this->listaragendamentos();        
      }*/
      
  }
