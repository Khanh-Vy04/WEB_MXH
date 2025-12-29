<!--newsletter strat -->
<section id="newsletter"  class="newsletter">
	<div class="container">
		<div class="hm-footer-details">
			<div class="row">
				<div class=" col-md-3 col-sm-6 col-xs-12">
					<div class="hm-footer-widget">
						<div class="hm-foot-title">
							<h4>Danh mục công việc</h4>
						</div><!--/.hm-foot-title-->
						<div class="hm-foot-menu">
							<ul>
			
									<li><a href="/WEB_MXH/user/genre/genres.php">Thiết kế</a></li>
									<li><a href="/WEB_MXH/user/genre/genres.php">Marketing & truyền thông</a></li>
									<li><a href="/WEB_MXH/user/genre/genres.php">Viết lách & dịch thuật</a></li>
									<li><a href="/WEB_MXH/user/genre/genres.php">Lập trình</a></li>
							
							</ul><!--/ul-->
						</div><!--/.hm-foot-menu-->
					</div><!--/.hm-footer-widget-->
				</div><!--/.col-->
				<div class=" col-md-3 col-sm-6 col-xs-12">
					<div class="hm-footer-widget">
						<div class="hm-foot-title">
							<h4>Chính sách UniWork</h4>
						</div><!--/.hm-foot-title-->
						<div class="hm-foot-menu">
							<ul>
								<li><a href="/WEB_MXH/user/index.php">Điều khoản và điều kiện sử dụng</a></li><!--/li-->
								<li><a href="/WEB_MXH/user/new-arrivals.php">Chính sách bảo mật</a></li><!--/li-->
								
							</ul><!--/ul-->
						</div><!--/.hm-foot-menu-->
					</div><!--/.hm-footer-widget-->
				</div><!--/.col-->
				<div class=" col-md-3 col-sm-6 col-xs-12">
					<div class="hm-footer-widget">
						<div class="hm-foot-title">
							<h4>Liên hệ với chúng tôi</h4>
						</div><!--/.hm-foot-title-->
						<div class="hm-foot-menu">
							<ul>
								<p>Gmail: support@uniwork.com</p>
								<p>Thứ 2 - Thứ 6: 9:30 - 18:30</p>
								<p>Thứ 7 -  Chủ Nhật, Ngày Lễ: Nghỉ</p>
							</ul><!--/ul-->
						</div><!--/.hm-foot-menu-->
					</div><!--/.hm-footer-widget-->
				</div><!--/.col-->
		</div><!--/.hm-footer-details-->

	</div><!--/.container-->

</section><!--/newsletter-->	
<!--newsletter end -->

<!--footer start-->
<footer id="footer"  class="footer">
	<div class="container">
		<div class="hm-footer-copyright text-center">
			<p>
				&copy; 2025 UniWork. Tất cả quyền được bảo lưu. Thiết kế và phát triển bởi <a href="#">UniWork Team</a>
			</p><!--/p-->
		</div><!--/.text-center-->
	</div><!--/.container-->

	<!-- Scroll to top button removed -->
	
</footer><!--/.footer-->
<!--footer end-->

<style>
/* Newsletter styles */
.newsletter{
    padding: 20px 0;  /* Giảm padding trên dưới */
    background: #7c6e78;
    min-height: 300px;  /* Thêm min-height để đảm bảo chiều cao tối thiểu */
    display: flex;
    align-items: center;
}
.newsletter, .newsletter * { color: #111 !important; }

/* Container và layout */
.newsletter .container { 
    padding-left: 15px;  /* Giảm padding */
    padding-right: 15px;
}
.newsletter .row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin: 0 -15px;  /* Negative margin để cân bằng padding của columns */
}
.newsletter .col-md-3 {
    padding: 0 15px;  /* Padding đều cho các cột */
}

/* Widget styling */
.hm-footer-widget {
    margin-bottom: 0;  /* Bỏ margin bottom */
    height: 100%;  /* Chiều cao 100% để các cột bằng nhau */
}
.newsletter .hm-footer-widget:first-child { margin-left: 0; }  /* Reset margin left */

.hm-foot-title {
    margin-bottom: 15px;  /* Giảm margin dưới tiêu đề */
}
.hm-foot-title h4 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 10px;
    text-align: left;
}

/* Menu và text content */
.hm-foot-menu ul, 
.hm-foot-para, 
.hm-foot-menu ul p {
    margin: 0;
    text-align: left;
    font-size: 14px;
    line-height: 1.6;
}

.hm-foot-menu ul li a {
    color: #111;
    font-size: 14px;
    font-weight: 400;
    margin-bottom: 8px;
    display: inline-block;
    transition: .3s;
}

.hm-foot-menu ul li a:hover {
    color: #e99c2e;
    transform: translateX(10px);
}

/* Footer styles */
.footer {
    background: #f8f9fd;
    padding: 20px 0;
    margin-top: 0;
}
.hm-footer-copyright p,
.hm-footer-copyright p a {
    color: #a5adb3;
    font-size: 14px;
    margin: 0;
}

/* Scroll to top styles removed */

/* Responsive adjustments */
@media (max-width: 991px) {
    .newsletter {
        padding: 30px 0;
    }
    .hm-footer-widget {
        margin-bottom: 30px;
    }
}
</style>
