<?php

namespace Botble\RealEstate\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\RealEstate\Forms\ProjectForm;
use Botble\RealEstate\Http\Requests\ProjectRequest;
use Botble\RealEstate\Repositories\Interfaces\FeatureInterface;
use Botble\RealEstate\Repositories\Interfaces\ProjectInterface;
use Botble\RealEstate\Services\SaveFacilitiesService;
use Botble\RealEstate\Services\StoreProjectCategoryService;
use Botble\RealEstate\Tables\ProjectTable;
use Exception;
use Illuminate\Http\Request;

class ProjectController extends BaseController
{
    protected ProjectInterface $projectRepository;

    protected FeatureInterface $featureRepository;

    public function __construct(ProjectInterface $projectRepository, FeatureInterface $featureRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->featureRepository = $featureRepository;
    }

    public function index(ProjectTable $dataTable)
    {
        page_title()->setTitle(trans('plugins/real-estate::project.name'));

        return $dataTable->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/real-estate::project.create'));

        return $formBuilder->create(ProjectForm::class)->renderForm();
    }

    public function store(
        ProjectRequest $request,
        BaseHttpResponse $response,
        StoreProjectCategoryService $projectCategoryService,
        SaveFacilitiesService $saveFacilitiesService
    ) {
        $request->merge(['images' => json_encode(array_filter($request->input('images', [])))]);

        $project = $this->projectRepository->create($request->input());

        if ($project) {
            $project->features()->sync($request->input('features', []));

            $saveFacilitiesService->execute($project, $request->input('facilities'));

            $projectCategoryService->execute($request, $project);
        }

        event(new CreatedContentEvent(PROJECT_MODULE_SCREEN_NAME, $request, $project));

        return $response
            ->setPreviousUrl(route('project.index'))
            ->setNextUrl(route('project.edit', $project->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int $id, Request $request, FormBuilder $formBuilder)
    {
        $project = $this->projectRepository->findOrFail($id);

        page_title()->setTitle(trans('plugins/real-estate::project.edit') . ' "' . $project->name . '"');

        event(new BeforeEditContentEvent($request, $project));

        return $formBuilder->create(ProjectForm::class, ['model' => $project])->renderForm();
    }

    public function update(
        ProjectRequest $request,
        int $id,
        BaseHttpResponse $response,
        StoreProjectCategoryService $projectCategoryService,
        SaveFacilitiesService $saveFacilitiesService
    ) {
        $project = $this->projectRepository->findOrFail($id);

        $request->merge(['images' => json_encode(array_filter($request->input('images', [])))]);

        $project->fill($request->input());

        $this->projectRepository->createOrUpdate($project);

        $project->features()->sync($request->input('features', []));

        $saveFacilitiesService->execute($project, $request->input('facilities'));

        $projectCategoryService->execute($request, $project);

        event(new UpdatedContentEvent(PROJECT_MODULE_SCREEN_NAME, $request, $project));

        return $response
            ->setPreviousUrl(route('project.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Request $request, int $id, BaseHttpResponse $response)
    {
        try {
            $project = $this->projectRepository->findOrFail($id);
            $this->projectRepository->delete($project);

            event(new DeletedContentEvent(PROJECT_MODULE_SCREEN_NAME, $request, $project));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $project = $this->projectRepository->findOrFail($id);
            $this->projectRepository->delete($project);

            event(new DeletedContentEvent(PROJECT_MODULE_SCREEN_NAME, $request, $project));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
