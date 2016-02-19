<?php
namespace Payum\Paymill\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($details['http_status_code'])) {
            $request->markNew();
            return;
        }

        // HTTP status
        if (200 != $details['http_status_code'] || !isset($details['transaction'])) {
            $request->markFailed();
            return;
        }

        // Response code
        $code = $details['transaction']['response_code'];

        if (10002 == $code) {
            $request->markPending();
            return;
        } elseif (20000 != $code) {
            $request->markFailed();
            return;
        }

        // Status of transaction
        $status = $details['transaction']['status'];
        switch ($status) {
            case 'open':
            case 'pending':
            case 'preauthorize':
            case 'preauth':
                $request->markPending();
                break;
            case 'closed':
                $request->markAuthorized();
                break;
            case 'failed':
                $request->markFailed();
                break;
            case 'refunded':
            case 'partial_refunded':
            case 'partially_refunded':
            case 'chargeback':
                $request->markRefunded();
                break;
            default:
                $request->markUnknown();
                break;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
