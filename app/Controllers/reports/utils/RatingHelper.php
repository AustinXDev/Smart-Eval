<?php 
class RatingHelper
{

  public static function getRatingCategory($score){
      if ($score >= 4.5) return ['text' => 'Excellent', 'color' => '#28a745'];
      if ($score >= 3.5) return ['text' => 'Very Good', 'color' => '#fd7e14'];
      if ($score >= 2.5) return ['text' => 'Good', 'color' => '#ffc107'];
      return ['text' => 'Fair', 'color' => '#dc3545'];
  }

  public static function participationRating($rate){
      if($rate >= 80) return ['color' => '#28a745'];
      if($rate >= 60) return ['color' => '#fd7e14'];
      if($rate >= 40) return ['color' => '#ffc107'];
      return ['color' => '#dc3545'];
  }
}
?>