<?php

class LinkedListElement
{
	private $prev;
	private $value;
	private $data;
	private $next;

	public function __construct( $pPrev, $pNext, $pValue, $pData )
	{
		$this->prev = $pPrev;
		$this->next = $pNext;
		$this->value  = $pValue;
		$this->data = $pData;
	}

	public function get_next_element()
	{
		return $this->next;
	}

	public function get_prev_element()
	{
		return $this->prev;
	}

	public function get_data()
	{
		return $this->data;
	}

	public function get_value()
	{
		return $this->value;
	}

	public function set_next_element( $pNext )
	{
		$this->next = $pNext;
	}

	public function set_prev_element( $pPrev )
	{
		$this->prev = $pPrev;
	}

}

class LinkedList
{
	private $tail_element;
	private $head_element;
	private $max_size;
	private $current_size;
	private $current_element;

	public function __construct( $pMaxSize )
	{
		$this->tail_element = NULL;
		$this->head_element = NULL;
		$this->max_size     = $pMaxSize;
		$this->current_size = 0;
		$this->current_element = $this->tail_element;
	}
	public function get_max_value()
	{
		if ( $this->head_element != NULL )
		{
			return $this->head_element->get_value();
		}
		else
		{
			return 0;
		}
	}

	private function get_head_element()
	{
		return $this->head_element;
	}

	private function get_tail_element()
	{
		return $this->tail_element;
	}

	public function get_min_value()
	{
		if ( $this->tail_element != NULL )
		{
			return $this->tail_element->get_value();
		}
		else
		{
			return 0;
		}
	}

	public function get_max_size()
	{
		return $this->max_size;
	}

	public function get_current_size()
	{
		return $this->current_size;
	}

	public function search_prev_element( $pValue )
	{
		if ( $pValue <= $this->get_min_value() )
		{
			return NULL;
		}
		if ( $pValue >= $this->get_max_value() )
		{
			return $this->head_element;
		}
		$this->reset();
		$prev_element = NULL;
		while ( $element = $this->get_element() )
		{
			if ( $element->get_value() >= $pValue )
			{
				break;
			}
			$prev_element = $element;
		}
		return $prev_element;
	}

	public function can_be_added( $pValue )
	{
		return ( ( $this->get_current_size() < $this->get_max_size() ) || ( ( $this->get_current_size() == $this->get_max_size() ) && $pValue > $this->get_min_value() ) );
	}

	public function add_element( $pValue, $pData = "" )
	{
		
		if ( $pValue <= $this->get_min_value() && $this->current_size == $this->max_size )
		{
			throw new Exception( "does not work" );
		}
		if ( $pValue <= $this->get_min_value() || $this->current_size == 0 )
		{
			if ( $this->tail_element != NULL )
			{
				$next_element = $this->tail_element;
				$element = new LinkedListElement(
						NULL,
						$next_element,
						$pValue,
						$pData
						);
				if ( $next_element != NULL )
				{
					$next_element->set_prev_element( $element );
				}
				$this->tail_element = $element;
			}
			else
			{
				// Allererstes Element
				$element = new LinkedListElement(
						NULL,
						NULL,
						$pValue,
						$pData
						);
				$this->head_element = $element;
				$this->tail_element = $element;
			}
		}
		elseif( $pValue >= $this->get_max_value() )
		{
			$element = new LinkedListElement(
				$this->get_head_element(),
				NULL,
				$pValue,
				$pData
			);
			$this->head_element->set_next_element( $element );
			$this->head_element = $element;
		}
		else
		{
			$prev_element = $this->search_prev_element( $pValue );
			$next_element = $prev_element->get_next_element();
			$element = new LinkedListElement(
				$prev_element,
				$next_element,
				$pValue,
				$pData
			);
			$prev_element->set_next_element( $element );
			$next_element->set_prev_element( $element );
		}
		$this->current_size += 1;
		if ( $this->current_size > $this->max_size )
		{
			$this->delete_element( $this->tail_element );
		}
		$this->current_element = $element;
	}

	public function delete_element( $pElement )
	{
		$prev_element = $pElement->get_prev_element();
		$next_element = $pElement->get_next_element();
		if ( $prev_element != NULL )
		{
			$prev_element->set_next_element( $next_element );
		}
		else
		{
			// pElement was this->tail_element
			$this->tail_element = $next_element;
		}
		if ( $next_element != NULL )
		{
			$next_element->set_prev_element( $prev_element );
		}
		else
		{
			// pElement was this->head_element
			$this->head_element = $prev_element;
		}
		$this->current_size -= 1;
	}

	public function reset( $pForward = TRUE )
	{
		if ( $pForward )
		{
			$this->current_element = $this->tail_element;
		}
		else
		{
			$this->current_element = $this->head_element;
		}
	}

	public function get_element( $pForward = TRUE )
	{
		$element = $this->current_element;
		if ( $element != NULL )
		{
			if ( $pForward )
			{
				$this->current_element = $element->get_next_element();
			}
			else
			{
				$this->current_element = $element->get_prev_element();
			}
		}
		return $element;
	}
}

?>
