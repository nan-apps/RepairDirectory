<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Tactician\CommandBus;
use TheRestartProject\RepairDirectory\Application\Commands\Business\ImportFromHttpRequest\ImportFromHttpRequestCommand;
use TheRestartProject\RepairDirectory\Application\Commands\Business\ImportFromHttpRequest\ImportFromHttpRequestFactory;
use TheRestartProject\RepairDirectory\Application\Exceptions\BusinessValidationException;
use TheRestartProject\RepairDirectory\Domain\Enums\Category;
use TheRestartProject\RepairDirectory\Domain\Enums\PublishingStatus;
use TheRestartProject\RepairDirectory\Domain\Enums\ReviewSource;
use TheRestartProject\RepairDirectory\Domain\Models\Business;
use TheRestartProject\RepairDirectory\Domain\Repositories\BusinessRepository;
use TheRestartProject\RepairDirectory\Domain\Services\ReviewManager;
use TheRestartProject\RepairDirectory\Infrastructure\Services\ReviewService\ReviewService;

class BusinessController extends Controller
{
    public function edit($id = null, BusinessRepository $repository)
    {
        $business = $id ? $repository->findById($id) : new Business();

        $this->authorize('view', $business);

        return $this->renderEdit($business, []);
    }

    public function create(Request $request, CommandBus $commandBus, ImportFromHttpRequestFactory $commandFactory)
    {
        try {
            $command = $commandFactory->makeFromRequest($request);
            $commandBus->handle($command);
        } catch (BusinessValidationException $e) {
            return $this->renderEdit($e->getBusiness(), $e->getErrors());
        }

        return redirect('map/admin');
    }

    public function update($id, Request $request, CommandBus $commandBus, ImportFromHttpRequestFactory $commandFactory)
    {
        try {
            $command = $commandFactory->makeFromRequest($request);
            $commandBus->handle(new ImportFromHttpRequestCommand($request->all(), $id));
        } catch (BusinessValidationException $e) {
            return $this->renderEdit($e->getBusiness(), $e->getErrors());
        }

        return redirect('map/admin');
    }

    public function scrapeReview(Request $request, ReviewManager $reviewManager)
    {
        $url = $request->input("url");
        $response = $reviewManager->getReviewResponse($url);
        if ($response) {
            $data = $response->toArray();
            return response($data, 200);
        }
        return response('', 404);
    }

    private function renderEdit(Business $business, $errors) {
        $isCreate = $business->getUid() === null;

        $formAction = $isCreate ? route('admin.business.create') : route('admin.business.update', ['id' => $business->getUid()]);
        $formMethod = $isCreate ? 'post' : 'put';

        if ($isCreate) {
            $business->setPublishingStatus(PublishingStatus::DRAFT);
        }

        $publishingStatuses = PublishingStatus::values();
        $authorizedStatuses = $publishingStatuses;


        if (auth()->user()->getRole() === 4) {
            $authorizedStatuses = [
                PublishingStatus::DRAFT,
                PublishingStatus::READY_FOR_REVIEW
            ];
        }

        return view('admin.business.edit', [
            'categories' => Category::values(),
            'reviewSources' => ReviewSource::values(),
            'publishingStatuses' => $publishingStatuses,
            'authorizedStatuses' => $authorizedStatuses,
            'business' => $business,
            'isCreate' => $isCreate,
            'formAction' => $formAction,
            'formMethod' => $formMethod,
            'errors' => $errors
        ]);
    }
}
