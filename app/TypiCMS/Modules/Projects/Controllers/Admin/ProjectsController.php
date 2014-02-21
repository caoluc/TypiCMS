<?php namespace TypiCMS\Modules\Projects\Controllers\Admin;

use App;
use View;
use Input;
use Config;
use Request;
use Session;
use Redirect;

use TypiCMS\Modules\Projects\Repositories\ProjectInterface;
use TypiCMS\Modules\Projects\Services\Form\ProjectForm;

use App\Controllers\Admin\BaseController;

class ProjectsController extends BaseController {

	public function __construct(ProjectInterface $project, ProjectForm $projectform)
	{
		parent::__construct($project, $projectform);
		$this->title['parent'] = trans_choice('modules.projects.projects', 2);
	}

	/**
	 * List models
	 * GET /admin/model
	 */
	public function index()
	{
		$models = $this->repository->getAll(true);
		$this->layout->content = View::make('projects.admin.index')
			->withModels($models);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$model = $this->repository->getModel();

		$categories = App::make('TypiCMS\Modules\Categories\Repositories\CategoryInterface')->getAllForSelect();

		$tags = Session::getOldInput('tags');

		$this->title['child'] = trans('modules.projects.New');
		$this->layout->content = View::make('projects.admin.create')
			->withCategories($categories)
			->withTags($tags)
			->withModel($model);
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($model)
	{

		$this->title['child'] = trans('modules.projects.Edit');

		$categories = App::make('TypiCMS\Modules\Categories\Repositories\CategoryInterface')->getAllForSelect();

		$tags = '';
		$model->tags->each(function($tag) use(&$tags)
		{
			$tags .= $tag->tag.', ';
		});
		$tags = substr($tags, 0, -2);

		$this->layout->content = View::make('projects.admin.edit')
			->withCategories($categories)
			->withTags($tags)
			->withModel($model);
	}


	/**
	 * Show resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($model)
	{
		return Redirect::route('admin.projects.edit', $model->id);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

		if ( $model = $this->form->save( Input::all() ) ) {
			return (Input::get('exit')) ? Redirect::route('admin.projects.index') : Redirect::route('admin.projects.edit', $model->id) ;
		}

		return Redirect::route('admin.projects.create')
			->withInput()
			->withErrors($this->form->errors());

	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($model)
	{

		Request::ajax() and exit($this->repository->update( Input::all() ));

		$data = Input::all();

		// add checkboxes data
		foreach (Config::get('app.locales') as $locale) {
			$data[$locale]['status'] = Input::get($locale.'.status');
		}

		if ( $this->form->update( $data ) ) {
			return (Input::get('exit')) ? Redirect::route('admin.projects.index') : Redirect::route('admin.projects.edit', $model->id) ;
		}
		
		return Redirect::route( 'admin.projects.edit', $model->id )
			->withInput()
			->withErrors($this->form->errors());
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function sort()
	{
		$sort = $this->repository->sort( Input::all() );
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($model)
	{
		if ( $this->repository->delete($model) ) {
			if ( ! Request::ajax()) {
				return Redirect::back();
			}
		}
	}


}