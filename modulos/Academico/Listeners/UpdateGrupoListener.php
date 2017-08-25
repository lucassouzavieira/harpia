<?php

namespace Modulos\Academico\Listeners;

use Moodle;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Modulos\Academico\Events\UpdateGrupoEvent;
use Modulos\Academico\Repositories\GrupoRepository;
use Modulos\Integracao\Events\UpdateSincronizacaoEvent;
use Modulos\Integracao\Repositories\AmbienteVirtualRepository;

class UpdateGrupoListener
{
    protected $grupoRepository;
    protected $ambienteVirtualRepository;

    public function __construct(
        GrupoRepository $grupoRepository,
        AmbienteVirtualRepository $ambienteVirtualRepository
    ) {
        $this->grupoRepository = $grupoRepository;
        $this->ambienteVirtualRepository = $ambienteVirtualRepository;
    }
    
    public function handle(UpdateGrupoEvent $event)
    {
        try {
            $grupo = $event->getData();

            // ambiente virtual vinculado à turma do grupo
            $ambiente = $this->ambienteVirtualRepository->getAmbienteByTurma($grupo->grp_trm_id);

            if ($ambiente) {
                $param = [];

                // url do ambiente
                $param['url'] = $ambiente->url;
                $param['token'] = $ambiente->token;
                $param['functioname'] = $event->getEndpoint();
                $param['action'] = 'UPDATE';

                $param['data']['group']['grp_id'] = $grupo->grp_id;
                $param['data']['group']['grp_nome'] = $grupo->grp_nome;

                $response = Moodle::send($param);

                $status = 3;

                if (array_key_exists('status', $response)) {
                    if ($response['status'] == 'success') {
                        $status = 2;
                    }
                }

                event(new UpdateSincronizacaoEvent($grupo, $status, $response['message'], 'UPDATE'));
            }
        } catch (ConnectException $exception) {
            if (env('app.debug')) {
                throw $exception;
            }

            event(new UpdateSincronizacaoEvent($event->getData(), 3, $exception->getMessage(), $event->getAction()));
        } catch (ClientException $exception) {
            if (env('app.debug')) {
                throw $exception;
            }

            event(new UpdateSincronizacaoEvent($event->getData(), 3, $exception->getMessage(), $event->getAction()));
        } catch (\Exception $exception) {
            if (env('app.debug')) {
                throw $exception;
            }

            event(new UpdateSincronizacaoEvent($event->getData(), 3, $exception->getMessage(), $event->getAction()));
        } finally {
            return true;
        }
    }
}
