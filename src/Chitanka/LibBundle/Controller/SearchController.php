<?php

namespace Chitanka\LibBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Chitanka\LibBundle\Pagination\Pager;
use Chitanka\LibBundle\Entity\SearchString;
use Chitanka\LibBundle\Util\String;

class SearchController extends Controller
{
	protected $responseAge = 86400; // 24 hours
	private $minQueryLength = 3;

	public function indexAction()
	{
		if (($query = $this->getQuery()) instanceof Response) {
			return $query;
		}

		$persons = $this->getPersonRepository()->getByNames($query['text']);
		$texts = $this->getTextRepository()->getByTitles($query['text']);
		$books = $this->getBookRepository()->getByTitles($query['text']);
		$series = $this->getSeriesRepository()->getByNames($query['text']);
		$sequences = $this->getSequenceRepository()->getByNames($query['text']);
		$work_entries = $this->getWorkEntryRepository()->getByTitleOrAuthor($query['text']);
		$labels = $this->getLabelRepository()->getByNames($query['text']);
		$categories = $this->getCategoryRepository()->getByNames($query['text']);

		$found = count($persons) > 0 || count($texts) > 0 || count($books) > 0 || count($series) > 0 || count($sequences) > 0 || count($work_entries) > 0 || count($labels) > 0 || count($categories) > 0;

		if ($found) {
			$this->logSearch($query['text']);
		} else {
			$this->responseStatusCode = 404;
		}

		$this->view = compact('query', 'persons', 'texts', 'books', 'series', 'sequences', 'work_entries', 'labels', 'categories', 'found');

		return $this->display('index');
	}


	public function personsAction($_format)
	{
		if (($query = $this->getQuery($_format)) instanceof Response) {
			return $query;
		}

		if (empty($query['by'])) {
			$query['by'] = 'name,orig_name';
		}
		$persons = $this->getPersonRepository()->getByQuery($query);
		if ( ! ($found = count($persons) > 0)) {
			$this->responseStatusCode = 404;
		}
		$this->view = compact('query', 'persons', 'found');

		return $this->display("persons.$_format");
	}


	public function textsAction($_format)
	{
		if (($query = $this->getQuery($_format)) instanceof Response) {
			return $query;
		}

		if (empty($query['by'])) {
			$query['by'] = 'title,subtitle,orig_title';
		}
		$texts = $this->getTextRepository()->getByQuery($query);
		if ( ! ($found = count($texts) > 0)) {
			$this->responseStatusCode = 404;
		}
		$this->view = compact('query', 'texts', 'found');

		return $this->display("texts.$_format");
	}


	public function booksAction($_format)
	{
		if (($query = $this->getQuery($_format)) instanceof Response) {
			return $query;
		}

		if (empty($query['by'])) {
			$query['by'] = 'title,subtitle,orig_title';
		}
		$books = $this->getBookRepository()->getByQuery($query);
		if ( ! ($found = count($books) > 0)) {
			$this->responseStatusCode = 404;
		}
		$this->view = compact('query', 'books', 'found');

		return $this->display("books.$_format");
	}


	public function seriesAction($_format)
	{
		if (($query = $this->getQuery($_format)) instanceof Response) {
			return $query;
		}

		if (empty($query['by'])) {
			$query['by'] = 'name,orig_name';
		}
		$series = $this->getSeriesRepository()->getByQuery($query);
		if ( ! ($found = count($series) > 0)) {
			$this->responseStatusCode = 404;
		}
		$this->view = compact('query', 'series', 'found');

		return $this->display("series.$_format");
	}


	public function sequencesAction($_format)
	{
		if (($query = $this->getQuery($_format)) instanceof Response) {
			return $query;
		}

		if (empty($query['by'])) {
			$query['by'] = 'name';
		}
		$sequences = $this->getSequenceRepository()->getByQuery($query);
		if ( ! ($found = count($sequences) > 0)) {
			$this->responseStatusCode = 404;
		}
		$this->view = compact('query', 'sequences', 'found');

		return $this->display('sequences');
	}


	private function getQuery($_format = 'html')
	{
		$request = $this->get('request')->query;
		$query = $request->get('q');

		if ( ! $query) {
			$this->view = array(
				'latest_strings' => $this->getSearchStringRepository()->getLatest(30),
				'top_strings' => $this->getSearchStringRepository()->getTop(30),
			);

			return $this->display("list_top_strings.$_format");
		}

		$query = String::fixEncoding($query);

		$matchType = $request->get('match');
		if ($matchType != 'exact' && mb_strlen($query, 'utf-8') < $this->minQueryLength) {
			$this->view['message'] = sprintf('Трябва да въведете поне %d знака.', $this->minQueryLength);
			$this->responseStatusCode = 400;

			return $this->display("message.$_format");
		}

		return array(
			'text' => $query,
			'by'    => $request->get('by'),
			'match' => $matchType,
		);
	}

	private function logSearch($query)
	{
		$searchString = $this->getSearchStringRepository()->findOneBy(array('name' => $query));
		if ( ! $searchString) {
			$searchString = new SearchString($query);
		}
		$searchString->incCount();
		$this->getEntityManager()->persist($searchString);
		$this->getEntityManager()->flush();
	}


	public function latestAction($limit = 10)
	{
		$this->responseAge = 600; // 10 minutes
		$this->view = array(
			'strings' => $this->getSearchStringRepository()->getLatest($limit),
		);

		return $this->display('top_strings');
	}
}
