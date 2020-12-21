<?php 
/* C:\xampp\htdocs\themes\tastyigniter-orange/_pages/local\reviews.blade.php */
class Pagic5fdce8bf54b2c734100433_bc474518af507e668f1d5f5c7da5c7e4Class extends \Main\Template\Code\PageCode
{

public function onStart()
{
    if (!View::shared('showReviews')) {
        flash()->error(lang('igniter.local::default.review.alert_review_disabled'))->now();

        return Redirect::to($this->controller->pageUrl($this['localReview']->property('redirectPage')));
    }
}

}
