<?php

namespace Botble\Location\Http\Controllers;

use BaseHelper;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Location\Http\Requests\CountryRequest;
use Botble\Location\Http\Resources\CountryResource;
use Botble\Location\Models\Country;
use Botble\Location\Repositories\Interfaces\CountryInterface;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Location\Tables\CountryTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Location\Forms\CountryForm;
use Botble\Base\Forms\FormBuilder;

class CountryController extends BaseController
{
    protected CountryInterface $countryRepository;

    public function __construct(CountryInterface $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function index(CountryTable $table)
    {
        page_title()->setTitle(trans('plugins/location::country.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/location::country.create'));

        return $formBuilder->create(CountryForm::class)->renderForm();
    }

    public function store(CountryRequest $request, BaseHttpResponse $response)
    {
        $country = $this->countryRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(COUNTRY_MODULE_SCREEN_NAME, $request, $country));

        return $response
            ->setPreviousUrl(route('country.index'))
            ->setNextUrl(route('country.edit', $country->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int $id, FormBuilder $formBuilder, Request $request)
    {
        $country = $this->countryRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $country));

        page_title()->setTitle(trans('plugins/location::country.edit') . ' "' . $country->name . '"');

        return $formBuilder->create(CountryForm::class, ['model' => $country])->renderForm();
    }

    public function update(int $id, CountryRequest $request, BaseHttpResponse $response)
    {
        $country = $this->countryRepository->findOrFail($id);

        $country->fill($request->input());

        $this->countryRepository->createOrUpdate($country);

        event(new UpdatedContentEvent(COUNTRY_MODULE_SCREEN_NAME, $request, $country));

        return $response
            ->setPreviousUrl(route('country.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Request $request, int $id, BaseHttpResponse $response)
    {
        try {
            $country = $this->countryRepository->findOrFail($id);

            $this->countryRepository->delete($country);

            event(new DeletedContentEvent(COUNTRY_MODULE_SCREEN_NAME, $request, $country));

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
            $country = $this->countryRepository->findOrFail($id);
            $this->countryRepository->delete($country);
            event(new DeletedContentEvent(COUNTRY_MODULE_SCREEN_NAME, $request, $country));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function getList(Request $request, BaseHttpResponse $response)
    {
        $keyword = BaseHelper::stringify($request->input('q'));

        if (! $keyword) {
            return $response->setData([]);
        }

        $data = $this->countryRepository->advancedGet([
            'condition' => [
                ['countries.name', 'LIKE', '%' . $keyword . '%'],
            ],
            'select' => ['countries.id', 'countries.name'],
            'take' => 10,
            'order_by' => ['order' => 'ASC', 'name' => 'ASC'],
        ]);

        $data->prepend(new Country(['id' => 0, 'name' => trans('plugins/location::city.select_country')]));

        return $response->setData(CountryResource::collection($data));
    }
}
