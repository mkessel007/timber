<?php

class TestTimberPagination extends Timber_UnitTestCase {

	function testPaginationSearch() {
		update_option( 'permalink_structure', '' );
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = false;
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '?s=post' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals( user_trailingslashit(home_url().'/?s=post&#038;paged=5'), $pagination['pages'][4]['link'] );
	}

	/* This test is for the concept of linking query_posts and get_pagination
	function testPaginationWithQueryPosts() {
		register_post_type( 'portfolio' );
		$pids = $this->factory->post->create_many( 33 );
		$pids = $this->factory->post->create_many( 55, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/' ) );
		Timber::query_posts('post_type=portfolio');
		$pagination = Timber::get_pagination();

		global $timber;
		$timber->active_query = false;
		unset($timber->active_query);
		$this->assertEquals(6, count($pagination['pages']));
	}
	*/

	function testPaginationWithGetPosts() {
		register_post_type( 'portfolio' );
		$pids = $this->factory->post->create_many( 33 );
		$pids = $this->factory->post->create_many( 55, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/' ) );
		Timber::get_posts('post_type=portfolio');
		$pagination = Timber::get_pagination();

		global $timber;
		$timber->active_query = false;
		unset($timber->active_query);
		$this->assertEquals(4, count($pagination['pages']));
	}

	function testPaginationOnLaterPage() {
		$struc = '/%postname%/';
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		register_post_type( 'portfolio' );
		$pids = $this->factory->post->create_many( 55, array( 'post_type' => 'portfolio' ) );
		$this->go_to( home_url( '/portfolio/page/3' ) );
		query_posts('post_type=portfolio&paged=3');
		$pagination = Timber::get_pagination();
		$this->assertEquals(6, count($pagination['pages']));
	}

	function testPaginationWithSize() {
		$this->setPermalinkStructure('/%postname%/');
		register_post_type( 'portfolio' );
		$pids = $this->factory->post->create_many( 99, array( 'post_type' => 'portfolio' ) );
		query_posts('post_type=portfolio');
		$pagination = Timber::get_pagination(4);
		$this->assertEquals(5, count($pagination['pages']));
	}

	function testPaginationSearchPrettyWithPostname() {
		$this->setPermalinkStructure('/%postname%/');
		$posts = $this->factory->post->create_many( 55 );
		$archive = home_url( '?s=post' );
		$this->go_to( $archive );
		query_posts( 's=post' );
		$pagination = Timber::get_pagination();
		$this->assertEquals( user_trailingslashit('http://example.org/page/5/?s=post'), $pagination['pages'][4]['link'] );
	}

	function testPaginationSearchPretty() {
		$struc = '/blog/%year%/%monthnum%/%postname%/';
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		update_option( 'permalink_structure', $struc );
		$posts = $this->factory->post->create_many( 55 );
		$archive = home_url( '?s=post' );
		$this->go_to( $archive );
		$pagination = Timber::get_pagination();
		$this->assertEquals( 'http://example.org/page/5/?s=post', $pagination['pages'][4]['link'] );
	}

	function testPaginationHomePrettyTrailingSlash() {
		$this->setPermalinkStructure('/%postname%/');
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '/' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals( user_trailingslashit('http://example.org/page/3/'), $pagination['pages'][2]['link'] );
		$this->assertEquals( user_trailingslashit('http://example.org/page/2/'), $pagination['next']['link'] );
	}

	function testPaginationHomePrettyNonTrailingSlash() {
		$this->setPermalinkStructure('/%postname%');
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '/' ) );
		$pagination = Timber::get_pagination();
		$this->assertEquals( 'http://example.org/page/3', $pagination['pages'][2]['link'] );
		$this->assertEquals( 'http://example.org/page/2', $pagination['next']['link'] );
	}

	function testPaginationInCategory( $struc = '/%postname%/' ) {
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		update_option( 'permalink_structure', $struc );
		$no_posts = $this->factory->post->create_many( 25 );
		$posts = $this->factory->post->create_many( 31 );
		$news_id = wp_insert_term( 'News', 'category' );
		foreach ( $posts as $post ) {
			wp_set_object_terms( $post, $news_id, 'category' );
		}
		$this->go_to( home_url( '/category/news' ) );
		$post_objects = Timber::get_posts( false );
		$pagination = Timber::get_pagination();
		//need to complete test
	}

	function testPaginationNextUsesBaseAndFormatArgs( $struc = '/%postname%/' ) {
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		update_option( 'permalink_structure', $struc );
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '/' ) );
		$pagination = Timber::get_pagination( array( 'base' => '/apricot/%_%', 'format' => 'page=%#%' ) );
		$this->assertEquals( '/apricot/page=2', $pagination['next']['link'] );
	}

	function testPaginationPrevUsesBaseAndFormatArgs( $struc = '/%postname%/' ) {
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		update_option( 'permalink_structure', $struc );
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( home_url( '/apricot/page=3' ) );
		query_posts('paged=3');
		$GLOBALS['paged'] = 3;
		$pagination = Timber::get_pagination( array( 'base' => '/apricot/%_%', 'format' => 'page=%#%' ) );
		$this->assertEquals( '/apricot/page=2', $pagination['prev']['link'] );
	}

	function testPaginationWithMoreThan10Pages( $struc = '/%postname%/' ) {
		global $wp_rewrite;
		$wp_rewrite->permalink_structure = $struc;
		update_option( 'permalink_structure', $struc );
		$posts = $this->factory->post->create_many( 150 );
		$this->go_to( home_url( '/page/13' ) );
		$pagination = Timber::get_pagination();
		$expected_next_link = user_trailingslashit('http://example.org/page/14/');
		$this->assertEquals( $expected_next_link, $pagination['next']['link'] );
	}
}
