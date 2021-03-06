<?php
/**
 * Created by PhpStorm.
 * User: eduardo
 * Date: 01/12/14
 * Time: 15:25
 */

namespace Cacic\RelatorioBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Cacic\WSBundle\Helper\TagValueHelper;
use Cacic\CommonBundle\Form\Type\ComputadorPesquisaType;
use Ddeboer\DataImport\Reader\ArrayReader;
use Ddeboer\DataImport\Writer\CsvWriter;
use Ddeboer\DataImport\ValueConverter\CallbackValueConverter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Ddeboer\DataImport\ValueConverter\CharsetValueConverter;
use Ddeboer\DataImport\Workflow;

class ComputadorController extends Controller {

    /**
     * Gera relatório com histórico de coletas para o computador/classe escolhidos
     *
     * @param Request $request
     * @param $idComputador
     * @param $classe
     * @return Response
     */
    public function historicoAction(Request $request, $idComputador, $classe) {

        $em = $this->getDoctrine()->getManager();
        $locale = $request->getLocale();
        $logger = $this->get('logger');

        $historico = $em->getRepository('CacicCommonBundle:ComputadorColetaHistorico')->listar(
            $limit = 10,
            $idComputador,
            $classe
        );

        $saida = array();
        foreach ($historico as $coletas) {
            #$logger->debug("333333333333333333333333333333 ".$coletas['nmPropertyName']. " ".$coletas['teClassPropertyValue']);
            $saida[$coletas['dtHrInclusao']->format('d-m-Y')][$coletas['nmPropertyName']] = TagValueHelper::getTableValues($coletas['teClassPropertyValue']);
        }

        return $this->render('CacicRelatorioBundle:Computador:historico.html.twig', array(
            'saida' => $saida,
            'classe' => $classe,
            'idioma' => $locale
        ));

    }

    /**
     * Rota para construir formulário de computadores
     *
     * @param Request $request
     * @return Response
     */
    public function listarAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $locale = $request->getLocale();

        $form = $this->createForm( new ComputadorPesquisaType() );

        return $this->render( 'CacicRelatorioBundle:Computador:listar.html.twig',
            array(
                'locale'=> $locale,
                'form' => $form->createView()
            )
        );

    }

    /**
     * Contador de computadores por subrede
     *
     * @param Request $request
     * @return Response
     */
    public function listarResultadoAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $locale = $request->getLocale();

        $data = $request->request->all();
        if (!empty($data)) {
            $inicio = $data['log_pesquisa']['dtAcaoInicio'];
            $fim = $data['log_pesquisa']['dtAcaoFim'];
            $idLocal = $data['log_pesquisa']['idLocal'];
        } else {
            $inicio = null;
            $fim = null;
            $idLocal = null;
        }

        $computadores = $em->getRepository("CacicCommonBundle:Computador")->listar($idLocal, $inicio, $fim);

        $TotalnumComp = 0;

        foreach ($computadores as $cont  ){
            $TotalnumComp += $cont['numComp'];
        }

        return $this->render( 'CacicRelatorioBundle:Computador:listarResultado.html.twig',
            array(
                'idioma'=> $locale,
                'filtroLocais' => $idLocal,
                'logs' => ( isset( $computadores ) ? $computadores : null ),
                'totalnumcomp' => $TotalnumComp,
                'inicio' => $inicio,
                'fim' => $fim
            )
        );

    }

    /**
     * Retorna CSV do total de computadores por subrede
     *
     * @param Request $request
     * @return BinaryFileResponse
     * @throws \Ddeboer\DataImport\Exception\ExceptionInterface
     * @throws \Exception
     */
    public function listarResultadoCsvAction(Request $request) {

        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();
        $locale = $request->getLocale();

        $data = $request->request->all();
        if (!empty($data)) {
            $inicio = $data['inicio'];
            $fim = $data['fim'];
            $idLocal = $data['idLocal'];
        } else {
            $inicio = null;
            $fim = null;
            $idLocal = null;
        }

        $computadores = $em->getRepository("CacicCommonBundle:Computador")->listar($idLocal, $inicio, $fim);

        $TotalnumComp = 0;

        foreach ($computadores as $cont  ){
            $TotalnumComp += $cont['numComp'];
        }

        // Gera CSV
        $reader = new ArrayReader($computadores);

        // Create the workflow from the reader
        $workflow = new Workflow($reader);

        $workflow->addValueConverter("reader",new CharsetValueConverter('UTF-8',$reader));

        // Add the writer to the workflow
        $tmpfile = tempnam(sys_get_temp_dir(), 'Computadores-Subredes');
        $file = new \SplFileObject($tmpfile, 'w');
        $writer = new CsvWriter($file);
        $writer->writeItem(array('Local', 'Subrede', 'IP da Subrede', 'Total de Estações'));
        $workflow->addWriter($writer);

        // Process the workflow
        $workflow->process();

        // Retorna o arquivo
        $response = new BinaryFileResponse($tmpfile);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="Faturamento_subrede.csv"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;

    }

    /**
     * Detalha os computadores de acordo com os filtros selecionados
     *
     * @param Request $request
     * @param null $idRede ID da Rede pra o filtro. Nulo para todas as redes
     * @return Response
     */
    public function listarDetalhadoAction(Request $request, $idRede = null) {
        $logger = $this->get('logger');

        $data = $request->request->all();
        if (!empty($data)) {
            $dataInicio = $data['log_pesquisa']['dtAcaoInicio'];
            $dataFim = $data['log_pesquisa']['dtAcaoFim'];
        } else {
            $dataInicio = null;
            $dataFim = null;
        }

        $locale = $request->getLocale();

        $dados = $this->getDoctrine()
            ->getRepository('CacicCommonBundle:Computador')
            ->gerarRelatorioComputadores($filtros = array(), $idRede, $dataInicio, $dataFim);

        if (!empty($idRede)) {
            $rede = $dados[0]['nmRede'];
        } else {
            $rede = null;
        }


        return $this->render(
            'CacicRelatorioBundle:Computador:listarDetalhado.html.twig',
            array(
                'rede'=> $rede,
                'idioma'=> $locale,
                'dados' => ( isset( $dados ) ? $dados : null ),
                'idRede' => $idRede,
                'dtAcaoInicio' => $dataInicio,
                'dtAcaoFim' => $dataFim
            )
        );
    }

    /**
     * CSV da lista de computadores
     *
     * @param Request $request
     * @param null $idRede ID da Rede
     * @return BinaryFileResponse
     * @throws \Ddeboer\DataImport\Exception\ExceptionInterface
     * @throws \Exception
     */
    public function listarDetalhadoCsvAction(Request $request, $idRede = null) {
        $dataInicio = $request->get('dataInicio');
        $dataFim = $request->get('dataFim');

        $locale = $request->getLocale();

        $dados = $this->getDoctrine()
            ->getRepository('CacicCommonBundle:Computador')
            ->gerarRelatorioComputadoresCsv($filtros = array(), $idRede, $dataInicio, $dataFim);

        if (!empty($idRede)) {
            $rede = $dados[0]['nmRede'];
        } else {
            $rede = null;
        }

        // Gera CSV
        $reader = new ArrayReader($dados);

        // Create the workflow from the reader
        $workflow = new Workflow($reader);

        $workflow->addValueConverter("reader",new CharsetValueConverter('UTF-8',$reader));

        $converter = new CallbackValueConverter(function ($input) {
            return $input->format('d/m/Y H:m:s');
        });
        $workflow->addValueConverter('dtHrUltAcesso', $converter);

        // Add the writer to the workflow
        $tmpfile = tempnam(sys_get_temp_dir(), 'Computadores-lista');
        $file = new \SplFileObject($tmpfile, 'w');
        $writer = new CsvWriter($file);
        $writer->writeItem(array('Computador','Mac Address','Endereço IP','Sistema Operacional','Local','Subrede','IP Subrede','Data/Hora do Ùltimo Acesso'));
        $workflow->addWriter($writer);

        // Process the workflow
        $workflow->process();

        // Retorna o arquivo
        $response = new BinaryFileResponse($tmpfile);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="Computadores.csv"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        return $response;
    }

} 