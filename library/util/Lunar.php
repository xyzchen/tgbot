<?php 
//===================================================
//    公历转换成农历（纯PHP实现版本）
//      陈逸少（jmchxy@gmail.com）
//           2011-07-30
//===================================================
class Lunar
{
	//----------------------------------------------------------
	// 常数定义
	//----------------------------------------------------------

	//------------------------------------------------
	//		下面是农历计算部分帮助函数
	//------------------------------------------------
	/* 公历每月前面的天数 */
	private static $_MonthAdd = array( // index =1 为1月
									0,	// 空白
									0,  31,  59,  90,  120,  151,	// 1 ~ 6月
									181,212, 243, 273, 304,  334	// 7 ~ 12月
								);
	/* 公历每月的天数  */
	private static $_MonthDays = array( // index =1 为1月
									0, 
									31, 28, 31, 30, 31, 30, // 1 ~ 6月
									31, 31, 30, 31, 30, 31	// 7 ~ 12月
								);//
	//----------------------------------------------------------
	/* 农历数据 */
	private static $_LunarInfo = array(// 时间原点为 1900-01-00(1899-12-31)为起点, 
		0x1F18096D, //1900年, 公历1900年1月31日春节(农历1900年正月初一), 以此为起点
		0x1320095C, 0x082014AE, 0x1D150A4D, 0x10201A4C, 0x04201B2A, 0x19140D55, 0x0D200AD4, 0x0220135A, 0x1612095D, 0x0A20095C,   // 1901 ～ 1910
		0x1E16149B, 0x1220149A, 0x06201A4A, 0x1A151AA5, 0x0E2016A8, 0x03201AD4, 0x171212DA, 0x0B2012B6, 0x01270937, 0x1420092E,   // 1911 ～ 1920
		0x08201496, 0x1C15164B, 0x10200D4A, 0x05200DA8, 0x181415B5, 0x0D20056C, 0x022012AE, 0x1712092F, 0x0A20092E, 0x1E160C96,	  // 1921 ～ 1930
		0x11201A94, 0x06201D4A, 0x1A150DA9, 0x0E200B5A, 0x0420056C, 0x1813126E, 0x0B20125C, 0x1F17192D, 0x1320192A, 0x08201A94,   // 1931 ～ 1940
		0x1B161B4A, 0x0F2016AA, 0x05200AD4, 0x1914155B, 0x0D2004BA, 0x0220125A, 0x1612192B, 0x0A20152A, 0x1D171695, 0x11200D94,   // 1941 ～ 1950
		0x062016AA, 0x1B150AB5, 0x0E2009B4, 0x032014B6, 0x18130A57, 0x0C200A56, 0x1F18152A, 0x12201D2A, 0x08200D54, 0x1C1715AA,   // 1951 ～ 1960
		0x0F20156A, 0x0520096C, 0x191414AE, 0x0D2014AE, 0x02200A4C, 0x15131D26, 0x09201B2A, 0x1E170B55, 0x11200AD4, 0x062012DA,   // 1961 ～ 1970
		0x1B15095D, 0x0F20095A, 0x0320149A, 0x17141A4D, 0x0B201A4A, 0x1F181AA5, 0x122016A8, 0x072016D4, 0x1C1612DA, 0x102012B6,   // 1971 ～ 1980
		0x05200936, 0x19141497, 0x0D201496, 0x022A164B, 0x14200D4A, 0x09200DA8, 0x1D1615B4, 0x1120156C, 0x0620126E, 0x1B15092F,   // 1981 ～ 1990
		0x0F20092E, 0x04200C96, 0x17130D4A, 0x0A201D4A, 0x1F180D65, 0x13200B58, 0x0720156C, 0x1C15126D, 0x1020125C, 0x0520192C,   // 1991 ～ 2000
		0x18141A95, 0x0C201A94, 0x01201B4A, 0x16120B55, 0x09200AD4, 0x1D17155B, 0x122004BA, 0x0720125A, 0x1A15192B, 0x0E20152A,   // 2001 ～ 2010
		0x03201694, 0x171416AA, 0x0A2015AA, 0x1F190AB5, 0x13200974, 0x082014B6, 0x1C160A57, 0x10200A56, 0x05201526, 0x19140E95,   // 2011 ～ 2020
		0x0C200D54, 0x012015AA, 0x161209B5, 0x0A20096C, 0x1D1612AE, 0x1120149C, 0x06201A4C, 0x1A151D26, 0x0D201AA6, 0x03200B54,   // 2021 ～ 2030
		0x17130D6A, 0x0B2012DA, 0x1F1B095D, 0x1320095A, 0x0820149A, 0x1C161A4B, 0x0F201A4A, 0x04201AA4, 0x18151B54, 0x0C2016B4,   // 2031 ～ 2040
		0x01200ADA, 0x1612095B, 0x0A200936, 0x1E171497, 0x11201496, 0x0620154A, 0x1A1516A5, 0x0E200DA4, 0x022015B4, 0x17130AB6,   // 2041 ～ 2050
		0x0B20126E, 0x0128092F, 0x1320092E, 0x08200C96, 0x1C160D4A, 0x0F201D4A, 0x04200D64, 0x1814156C, 0x0C20155C, 0x0220125C,   // 2051 ～ 2060
		0x1513192E, 0x0920192C, 0x1D171A95, 0x11201A94, 0x05201B4A, 0x1A150B55, 0x0E200AD4, 0x032014DA, 0x17140A5D, 0x0B200A5A,   // 2061 ～ 2070
		0x1F18152B, 0x1320152A, 0x07201694, 0x1B1616AA, 0x0F2015AA, 0x05200AB4, 0x181414BA, 0x0C2014B6, 0x02200A56, 0x16131527,   // 2071 ～ 2080
		0x09200D26, 0x1D170E53, 0x11200D54, 0x062015AA, 0x1A1509B5, 0x0E20096C, 0x032014AE, 0x18140A4E, 0x0A201A4C, 0x1E181D26,   // 2081 ～ 2090
		0x12201AA4, 0x07201B54, 0x1B160D6A, 0x0F200ADA, 0x0520095C, 0x1914149D, 0x0C20149A, 0x01201A2A, 0x15121B25, 0x09201AA4,   // 2091 ～ 2100
	);
	//---------------------------------------------------------
	// 公历数据
	public  $year; 	//公历: 年, 十进制，例如 2000
	public  $month;	//公历: 月, 十进制(1~12)，例如 10
	public  $day;		//公历: 日, 十进制(1~31)，例如 1
	public  $weekday; //公历: 星期, 整数 , 0 - 星期日, 1~6 - 星期一 ~ 星期六
	// 农历数据
	public  $lunarYear;	 // 农历年，如 2000
	public  $lunarMonth;  // 农历月，如 12, 1 ~ 12
	public  $lunarDay;	 // 农历日，如 13, 1 ~ 30
	public  $lunarIsLeapMonth; // 是否是闰月， 1 为农历闰月, 0 为非闰月
	/**
	 +----------------------------------------------------------
     * 架构函数
     * 创建一个 Lunar 对象
     +----------------------------------------------------------
     */
    public function __construct($year='', $month='', $day='') 
	{
		//获取当前日期
		$datestring = date("Y-m-d");
        list($y, $m, $d) = preg_split("/-/", $datestring);
        //检测参数
		if(empty($year))
		{
			$year  = intval($y, 10);
		}
		if(empty($month))
		{
			$month  = intval($m, 10);
		}
		if(empty($day))
		{
			$day  = intval($d, 10);
		}
        //使用指定日期
        $this->getLunarDate($year, $month, $day);
    }
	///////////////////////////////////////////////////////////////////
	//--------------------------------------------------------
	//    从公历到农历计算, 
	//        当天的农历年数应该和y相等
	//---------------------------------------------------------
	public function getLunarDate($y='', $m='', $d='')
	{
		if(empty($y))
		{
			$y = $this->year;
        }
        if(empty($m))
		{
            $m = $this->month;
        }
        if(empty($d))
		{
            $d = $this->day;
        }
    	$y = ($y < 1900) ? 1900 : $y;
        $this->year  = $y;
        $this->month = $m;
        $this->day   = $d;
		// 计算当年的春节日期
		$sy = $y;	//假设在春节之后
		list($sm, $sd) = self::getSpringDate($y);
		// 计算公历日期是否在当年春节之前	
		if(($sm > $m) || (($sm == $m) && ( $sd > $d)))
		{	// 在春节之前, 
			//1900春节之前的特殊处理
			if( $y == 1900)
			{
				// 此时只有可能是 1900-1-1 ~ 1900-1-30
				//   对应农历 1899-12-1 ~ 1899-12-30
				$this->lunarYear  = 1899;		//年
				$this->lunarMonth = 12;			//月
				$this->lunarDay   = $day;		//日
				$this->lunarIsLeapMonth = 0;	//非闰月
				$this->weekday    = $day % 7;	//星期
				return $this;
			}
			else
			{	// 不是 1900
				$sy = $y -1;	// 春节在前一年
				list($sm, $sd) = self::getSpringDate($sy);	//重新计算春节
			}
		}
		// 计算当天是 1900-1-1 后的第几天(1900-1-1为1)
		$offdays1 = self::getOffsetSolarDays($this->year, $this->month, $this->day);
		
		// 计算当年春节是 1900-1-1 后的第几天(1900-1-1为1)
		$offdays2 = self::getOffsetSolarDays($sy, $sm, $sd);

		// 计算星期几, 并填充结构
		$this->weekday	= $offdays1 % 7;

		// 填充农历年数
		$this->lunarYear = $sy;

		//--------------------------------------
		// 当天到农历当年春节的天数
		//   当天到当天的天数为1!
		$offdays = $offdays1 - $offdays2 + 1;

		// 然后计算农历月日
		$lm  = 1;	//农历月计数

		//   计算月
		while( $offdays > self::getLunarMonthDays($sy, $lm) )
		{
			// 减去当月天数
			$offdays -= self::getLunarMonthDays($sy, $lm);
			$lm++; // 农历月计数+1
		}
		// 剩下的不足一个月的天数为日期
		// 填充农历日
		$this->lunarDay = $offdays;

		// 填充月份
		// 计算当年是否有闰月
		$leapm = self::getLeapMonth($sy);
		if( $leapm == 0 )
		{	// 当年没有闰月
			$this->lunarMonth  = $lm;
			$this->lunarIsLeapMonth = 0;	//不是闰月
		}
		else	// 当年有闰月
		{
			if( $lm <= $leapm )
			{
				// 在闰月之前
				$this->lunarMonth  = $lm;
				$this->lunarIsLeapMonth = 0;	//不是闰月
			}
			else if( $lm == $leapm + 1) //闰月
			{
				// 闰月, 月份-1, 标记是闰月
				$this->lunarMonth  = $lm - 1;
				$this->lunarIsLeapMonth = 1;	//是闰月
			}
			else
			{
				// 在闰月之后, 月份-1
				$this->lunarMonth  = $lm - 1;
				$this->lunarIsLeapMonth = 0;	//不是闰月
			}
		}
		return $this;
	}
	//-----------------------------------------------------------
	//  完成从 农历 到 公历的转换
	//     参数:
	//		$ly, $lm, $ld: 农历年月日
	//-----------------------------------------------------------
	public function getSolarDate($ly, $lm, $ld, $isleapmonth = 0)
	{
		// 填充农历年月日
		$this->lunarYear  = $ly;		//年
		$this->lunarMonth = $lm;		//月
		$this->lunarDay   = $ld;		//日
		$this->lunarIsLeapMonth = $isleapmonth;//闰月
		
		// 月份修正
		$leapm = self::getLeapMonth($ly);
		if($leapm != 0)	//当年有闰月
		{
			if($lm > $leapm)
			{
				$lm++;
			}
			else if( ($lm == $leapm) && ($isleapmonth != 0) )
			{
				$lm++;
			}
			else
			{
				;//在闰月之前, 不用修正
			}
		}
		// 计算农历日期到 公历1900-1-1的天数(到农历1900-1-1,然后加30天)
		$days = self::getOffsetLunarDays($ly, $lm, $ld) + 30;
		
		// 填充星期数
		$this->weekday = $days % 7;

		// 计算公历日期
		$year  = 1900;
		$month = 1;
		$day   = 1;
		
		// 每年的天数
		$subdays = self::getSolarYearDays($year);
		
		// 定年
		while( $days > $subdays )
		{
			$year ++;
			$days -= $subdays;
			$subdays = self::getSolarYearDays($year);
		}
		// 定月
		$subdays = self::getSolarMonthDays($year, $month);
		while( $days > $subdays)
		{
			$month ++;
			$days -= $subdays;
			$subdays = self::getSolarMonthDays($year, $month);
		}
		$day = $days;

		// 填充公历年月日
		$this->year  = $year;
		$this->month = $month;
		$this->day   = $day;
		//返回
		return $this;
	}
	
	//-----------------------------------------------------------
	//     获取 距离 1900年1月1日 days 天的日期
	//         1900年1月1日为1
	//-----------------------------------------------------------
	public function getOffsetDate($days)
	{
		// 计算公历日期
		$year  = 1900;
		$month = 1;
		$day   = 1;
		
		// 定年
		$subdays = self::getSolarYearDays($year);
		while( $days > $subdays )
		{
			$year ++;
			$days   -= $subdays;
			$subdays = self::getSolarYearDays($year);
		}
		// 定月
		$subdays = self::getSolarMonthDays($year, $month);
		while( $days > $subdays)
		{
			$month ++;
			$days -= $subdays;
			$subdays = self::getSolarMonthDays($year, $month);
		}
		$day = $days;
		// 计算农历
		return $this->getLunarDate($year, $month, $day);
	}

	//----------------------------------------------
	//  增加或减少 $d 天
	//----------------------------------------------
	public function getDiffDate($d)
	{
		if(empty($d) ||($d == 0))
		{
			return $this;
		}
		// 计算当天是 1900-1-1 后的第几天(1900-1-1为1)
		$offdays1 = self::getOffsetSolarDays($this->year, $this->month, $this->day) + $d;
		// 计算星期几, 并填充结构
		$this->weekday	= $offdays1 % 7;
		// 重新计算年月日
		// 计算公历日期
		$year  = 1900;
		$month = 1;
		$day   = 1;
		// 每年的天数
		$subdays = self::getSolarYearDays($year);
		// 定年
		$days = $offdays1;
		while( $days > $subdays )
		{
			$year ++;
			$days -= $subdays;
			$subdays = self::getSolarYearDays($year);
		}
		// 定月
		$subdays = self::getSolarMonthDays($year, $month);
		while( $days > $subdays)
		{
			$month ++;
			$days -= $subdays;
			$subdays = self::getSolarMonthDays($year, $month);
		}
		// 定日
		$day = $days;
		// 填充公历年月日
		$this->year  = $year;
		$this->month = $month;
		$this->day   = $day;
		//---------------------------------
		// 计算农历
		//---------------------------------
		// 计算当年的春节日期
		$sy = $year;	//假设在春节之后
		list($sm, $sd) = self::getSpringDate($year);
		// 计算公历日期是否在当年春节之前	
		if(($sm > $month) || (($sm == $month) && ( $sd > $day)))
		{	// 在春节之前, 
			//1900春节之前的特殊处理
			if( $year == 1900)
			{
				// 此时只有可能是 1900-1-1 ~ 1900-1-30
				//   对应农历 1899-12-1 ~ 1899-12-30
				$this->lunarYear  = 1899;		//年
				$this->lunarMonth = 12;			//月
				$this->lunarDay   = $day;		//日
				$this->lunarIsLeapMonth = 0;	//非闰月
				return $this;
			}
			else
			{	// 不是 1900
				$sy = $year -1;	// 春节在前一年
				list($sm, $sd) = self::getSpringDate($sy);	//重新计算春节
			}
		}
		// 计算当年春节是 1900-1-1 后的第几天(1900-1-1为1)
		$offdays2 = self::getOffsetSolarDays($sy, $sm, $sd);
		// 填充农历年数
		$this->lunarYear = $sy;
		//--------------------------------------
		// 当天到农历当年春节的天数
		//   当天到当天的天数为1!
		//--------------------------------------
		$offdays = $offdays1 - $offdays2 + 1;
		// 然后计算农历月日
		$lm  = 1;	//农历月计数
		//   计算月
		while( $offdays > self::getLunarMonthDays($sy, $lm) )
		{
			// 减去当月天数
			$offdays -= self::getLunarMonthDays($sy, $lm);
			$lm++; // 农历月计数+1
		}
		// 剩下的不足一个月的天数为日期
		// 填充农历日
		$this->lunarDay = $offdays;
		// 填充月份
		// 计算当年是否有闰月
		$leapm = self::getLeapMonth($sy);
		if( $leapm == 0 )
		{	// 当年没有闰月
			$this->lunarMonth  = $lm;
			$this->lunarIsLeapMonth = 0;	//不是闰月
		}
		else	// 当年有闰月
		{
			if( $lm <= $leapm )
			{
				// 在闰月之前
				$this->lunarMonth  = $lm;
				$this->lunarIsLeapMonth = 0;	//不是闰月
			}
			else if( $lm == $leapm + 1) //闰月
			{
				// 闰月, 月份-1, 标记是闰月
				$this->lunarMonth  = $lm - 1;
				$this->lunarIsLeapMonth = 1;	//是闰月
			}
			else
			{
				// 在闰月之后, 月份-1
				$this->lunarMonth  = $lm - 1;
				$this->lunarIsLeapMonth = 0;	//不是闰月
			}
		}
		return $this;
	}
	
	//----------------------------------------------
	//  后一天
	//----------------------------------------------
	public function getNextDate()
	{
		// 首先调整公历数据:
		$this->weekday = ($this->weekday + 1) % 7;//星期数
		$this->day     = $this->day + 1;	//天数 +1
		// 调整日期
		if($this->day > self::getSolarMonthDays($this->year, $this->month))
		{
			// 超出了当月范围, 下个月1号
			$this->day   = 1;
			$this->month = $this->month + 1; //月+1
			// 如果月超出范围, 下一年1月
			if( $this->month > 12 )
			{
				$this->month = 1;	//
				$this->year  = $this->year + 1;
			}
		}
		//----------------------------------
		//调整农历数据
		//----------------------------------
		$ld = $this->lunarDay + 1; //天数 +1
		$lm = $this->lunarMonth;
		$ly = $this->lunarYear;
		// 如果是闰月, 特殊处理
		if($this->lunarIsLeapMonth)
		{
			// 当月最大天数
			$md = self::getLunarMonthIsBig($ly, $lm + 1) ? 30 : 29;
			// 如果超出, 下一个月
			if( $ld > $md)
			{
				$ld = 1;						//1号
				$lm = $lm + 1;					// 下一个月
				$this->lunarIsLeapMonth = 0;	// 当月是闰月, 下一月一定不是闰月
				// 检查月是否超出范围
				if( $lm > 12)
				{
					$lm = 1;		//1月
					$ly += 1;	//年 +1
				}
			}
			// 填充农历年月日
			$this->lunarDay	  = $ld;
			$this->lunarMonth = $lm;
			$this->lunarYear  = $ly;
		}
		else
		{// 当月不是闰月
			// 当年有闰月否 ?
			$pl = self::getLeapMonth($ly);
			if( $pl == 0)	// 当年也没有闰月
			{
				// 当月最大天数
				$md = self::getLunarMonthIsBig($ly, $lm) ? 30 : 29;
				// 如果超出, 下一个月
				if( $ld > $md)
				{
					$ld = 1;						//1号
					$lm = $lm + 1;					// 下一个月
					// 检查月是否超出范围
					if( $lm > 12)
					{
						$lm = 1;		//1月
						$ly += 1;	//年 +1
					}
				}
				// 填充农历年月日
				$this->lunarDay	  = $ld;
				$this->lunarMonth = $lm;
				$this->lunarYear  = $ly;
			}
			else // 当年有闰月, 情况比较复杂, 直接重新计算农历好了
			{
				$this->getLunarDate($this->year, $this->month, $this->day);
			}
		}
		return $this;
	}
	/**
     +----------------------------------------------------------
     *  判断日期 所属 星期 星座 干支 生肖 月 日
     *  type 参数：WN 星期 XZ 星座 GZ 干支 SX 生肖  MN 月 DN 日 
     *
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $type  获取信息类型
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	public function magicInfo($type)
	{
		// 公历数据    
        $y  = $this->year;
        $m  = $this->month;        
        $d  = $this->day;         
        $w  = $this->weekday;
		// 农历数据
		$ly =  $this->lunarYear;	// 农历年，如 2000
		$lm =  $this->lunarMonth;	// 农历月，如 12, 1 ~ 12
		$ld =  $this->lunarDay;		// 农历日，如 13, 1 ~ 30
		$lpm = $this->lunarIsLeapMonth; // 是否是闰月， 1 为农历闰月, 0 为非闰月       
      	 
		$result = '';
        switch ($type)
        {
        case 'WN'://星期
        	$WNDict = array("日","一","二","三","四","五","六");
            $result = '星期' . $WNDict[$w];
            break;
            
        case 'XZ'://星座
            $XZDict = array('摩羯','宝瓶','双鱼','白羊','金牛','双子','巨蟹','狮子','处女','天秤','天蝎','射手');
            $Zone   = array(1222,122,222,321,421,522,622,722,822,922,1022,1122,1222);
            if((100*$m+$d)>=$Zone[0]||(100*$m+$d)<$Zone[1])
            {
            	 $i=0;
            }                
            else
            {
                for($i=1;$i<12;$i++)
                {
                	if((100*$m+$d)>=$Zone[$i]&&(100*$m+$d)<$Zone[$i+1])
                  		break;
                }
            }
            $result = $XZDict[$i].'座';
            break;

        case 'GZ'://干支, 用农历年计算
            $GZDict = array(
                        array('甲','乙','丙','丁','戊','己','庚','辛','壬','癸'),
                        array('子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥')
                        );
            $i= $ly -1900 + 36 ;
            $result = $GZDict[0][$i%10].$GZDict[1][$i%12];
            break;

        case 'SX'://生肖, 用农历年计算
            $SXDict = array('鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪');
            $result = $SXDict[($ly-4)%12];
            break;
                       
       case 'MN'://农历月份
        	$MNDict = array('', '正','二','三','四','五','六','七','八','九','十','冬','腊');
            if($lpm == 1)
            {
            	$result = '闰' . $MNDict[$lm]. '月';	
            }
            else
            {
            	$result = $MNDict[$lm]. '月';	
            }
            break;
        
        case 'DN'://农历日期
        	$DNDict =  array(   '',
								'初一','初二','初三','初四','初五','初六','初七','初八','初九','初十',
								'十一','十二','十三','十四','十五','十六','十七','十八','十九','二十',
								'廿一','廿二','廿三','廿四','廿五','廿六','廿七','廿八','廿九','三十'
							);
			$result = $DNDict[$ld];
			break;
        }
        return $result;
    }

	//----------------------------------
	//获取当天的农历传统节日字符串
	//----------------------------------
	public function getLunarHolidayName()
	{
		/* 农历传统节日数据　*/
		$stLunarHoliday = array(
			array(0,  0, ""),//0, 无节日
			array(1,  1,  "春节"),//1
			array(1,  15, "元霄节"),//2
			array(5,  5,  "端午节"),//3
			array(7,  7,  "七夕节"),//4
			array(8,  15, "中秋节"),//5
			array(9,  9,  "重阳节"),//6
			array(12, 8,  "腊八节"),//7
		);
		for($id = 0; $id < count($stLunarHoliday); $id++)
		{
			if(($this->lunarMonth == $stLunarHoliday[$id][0])&&($this->lunarDay == $stLunarHoliday[$id][1]))
			{
				return $stLunarHoliday[$id][2];
			}
		}
		return false;
	}
	
	//----------------------------------
	//获取当天的公历节日字符串
	//----------------------------------
	public function getSolarHolidayName()
	{
		/* 公历节日数据　*/
		$stSolarHoliday = array(
			array(0,  0, ""),//0, 无节日
			array(1,  1,  "元旦"),//1
			array(2,  14, "情人节"),
			array(3,  8,  "妇女节"),//3
			array(4,  1,  "愚人节"),
			array(5,  4,  "青年节"),//5
			array(6,  1,  "儿童节"),//
			array(7,  1,  "建党节"),//
			array(8,  1,  "建军节"),//
			array(9,  10, "教师节"),//
			array(10, 1,  "国庆节"),//10
			array(12, 25, "圣诞节"),
		);
		for($id = 0; $id < count($stSolarHoliday); $id++)
		{
			if(($this->month == $stSolarHoliday[$id][0])&&($this->day == $stSolarHoliday[$id][1]))
			{
				return $stSolarHoliday[$id][2];
			}
		}
		return false;
	}
	
	//----------------------------------------------------
    //获取当前日期字符串(长字符串)
    //-----------------------------------------------------
	public function getDateString()
	{
		//农历日期
		$strday  = $this->year . '年' . $this->month . '月' . $this->day . '日';
		
		$strweek = $this->magicInfo('WN');
		$strgz   = $this->magicInfo('GZ');
		$strsx   = $this->magicInfo('SX');
		$strmn   = $this->magicInfo('MN');
		$strdn   = $this->magicInfo('DN');
		//农历日期
		$strlday =  $strgz . '(' . $strsx .')年' .  $strmn . $strdn;
		//返回字符串
	    return $strday . ' ' . $strlday . ' ' . $strweek;
	}
	
	//----------------------------------------------------
	//获取当前日期字符串(短字符串，用于月历中显示)
	// $format = 1时显示月份，0时不显示月份
	//----------------------------------------------------
	public function getDateName($format=0)
	{
		$datename = Lunar::getTermName($this->year, $this->month, $this->day);
		if(!$datename)
		{
			if($format==0)
			{
				if($this->lunarDay == 1)
				{
					$datename = $this->magicInfo('MN');
				}
				else
				{
					$datename = $this->magicInfo('DN');
				}
			}
			else
			{
				$datename = $this->magicInfo('MN') . $this->magicInfo('DN');
			}
		}
		return $datename;
	}
	////////////////////////////////////////////////////////////////////////
	///下面的是静态函数
	////////////////////////////////////////////////////////////////////////
    //--------------------------------------------
	// 计算 公历 y年m月的天数   
	//     参数:  y  - 年, 1900 ~ 2100
	//            m  - 月, 1    ~ 12
	//--------------------------------------------
	public static function getSolarMonthDays($y, $m) 
	{
		if( $m == 2 )  // 二月, 特殊处理
		{
		   //公历闰年
		   return(( (($y % 4 == 0) && ($y % 100 != 0)) || ($y % 400 == 0))? 29 : 28);
		}
		return self::$_MonthDays[$m];	//平年
	}
	
	//-------------------------------------------
	// 计算公历某年的天数
	//-------------------------------------------
	public static function getSolarYearDays($y)
	{
		if((($y % 4 == 0) && ($y % 100 != 0)) || ($y % 400 == 0))
		{
			return 366;	// 公历闰年
		}
		return 365;	//公历平年
	}
		
	//------------------------------------------------------
	//  计算 公历 y-m-d 到 1900-1-0的天数
	//   
	//     参数: 公历的年月日 1900-1-1 ~ 2100-12-31 之间
	//
	//     返回:
	//          天数, 一个正数值 1900-1-1为 1
	//          
	//-----------------------------------------------------
	public static function getOffsetSolarDays($y, $m, $d)
	{
		$days = 0;
		// 对1900年特别处理
		if($y == 1900)
		{
			// 本年不是闰年
			$days += self::$_MonthAdd[$m];
			$days += $d;
		}
		else
		{
			$days += ($y - 1900) * 365;				// 加之前 年数  * 365
			$days += (int)floor(($y - 1901) / 4);	// 加闰年数
			// 加当年的天数
			$days += self::$_MonthAdd[$m];	// 加上当月之前的天数
			$days += $d;					// 加上当月天数

			// 如果当年是闰年, 并且2月之后, 天数加1
			if(((($y % 4 == 0) && ($y % 100 != 0)) || ($y % 400 == 0)) && ($m > 2))
			{
				$days++;
			}
		}
		return $days;
	}
	
	//-------------------------------------------------------
	//  计算公历 y 年 m 月 d 日 是星期几 (0=星期天)
	//-------------------------------------------------------
	public static function getWeekday($y, $m, $d)
	{
		// 计算到初始时间 1900年1月1日的天数：1900-1-1(星期一)---*/
		return (self::getOffsetSolarDays($y, $m, $d) % 7);
	}
	
	//-------------------------------------------------------
	//   返回农历 y 年 闰几月(1 ~ 12), 没有返回0
	//-------------------------------------------------------
	public static function getLeapMonth($y)
	{
		return ((self::$_LunarInfo[$y - 1900] >> 16) & 0xf);
	}
	
	//-------------------------------------------------------
	//   返回农历 y年m月 是大月还是小月
	//
	//   参数:
	//         y = 1900 ~ 2100(指的是农历)
	//         m = 1 ~ 13(指的是农历): 
	//   返回:
	//         1 : 月大
	//         0 : 月小
	//-------------------------------------------------------
	public static function getLunarMonthIsBig($y, $m)
	{     
		$moninfo = self::$_LunarInfo[$y - 1900] & 0x1fff;
		return (($moninfo >> (13 -$m)) & 0x1);
	}
	
	//-------------------------------------------------------
	//  计算农历某年某月的天数
	//      月分 1~ 13, 必须有13个月时13才有意义
	//-------------------------------------------------------
	public static function getLunarMonthDays($lunarY, $lunarM)
	{
		return (self::getLunarMonthIsBig($lunarY, $lunarM) ? 30 : 29);
	}
	
	//-------------------------------------------------------
	// 计算农历某年的天数
	//-------------------------------------------------------
	public static function getLunaYearDays($lunarY)
	{
		$days = 0;
		
		$leapm = self::getLeapMonth($lunarY);
		// 全部按小月计算
		if($leapm != 0)
		{
			$days = 13 * 29;
		}
		else
		{
			$days = 12 * 29;
		}
		// 增加大月的数据
		$moninfo = self::$_LunarInfo[$lunarY - 1900] & 0x1fff;
		for($m = 1; $m <= 13; $m++)
		{
			if( (( $moninfo >> (13 - $m)) & 0x1) == 0x1 )
			{
				$days ++;
			}
		}
		return $days;
	}
	
	//-------------------------------------------------------
	//  计算农历 y年 春节的 公历日期
	//      返回: list(month, day)
	//-------------------------------------------------------
	public static function getSpringDate($y)
	{
		//  农历春节的公历月份
		$m = ((self::$_LunarInfo[$y - 1900] >> 20) & 0xf);
		//  农历春节的公历日
		$d = ((self::$_LunarInfo[$y - 1900] >> 24) & 0xff);
		// 返回
		return array($m, $d);
	}
	
	//---------------------------------------------------------------
	//  计算农历 y年m月d日到农历 1900年正月初一(公历1900-1-31)的天数
	//    当天到当天为 1
	//    这里不区分闰月, 农历月份为 1 ~ 13
	//---------------------------------------------------------------
	public static function getOffsetLunarDays($year, $month, $day)
	{
		$days = 0;
		// 计算 year 之前的整年数
		for($y = 1900; $y < $year; $y++)
		{
			$days += self::getLunaYearDays($y);
		}
		// 计算之前的月的
		for($m = 1; $m < $month; $m++)
		{
			$days += self::getLunarMonthDays($year, $m);
		}
		// 加当月天数
		$days += $day;
		return $days;
	}
	
	////////////////////////////////////////////////////////////
	//  其他的辅助函数
	////////////////////////////////////////////////////////////
	//-------------------------------------------------------
	//获取指定年,月的月历第一天(从星期天开始)
	//-------------------------------------------------------
	public static function getMonthFirstday($year, $month)
	{
		$lu = new Lunar($year, $month, 1);
		if($lu->weekday != 0)
		{
			$lu->getDiffDate(0 - $lu->weekday);
		}
		return $lu;
	}
	////////////////////////////////////////////////////////////
	//  24节气计算
	////////////////////////////////////////////////////////////
	//-----------------------------------------------------
	//    计算 y 年的第n个节气是几号(从0小寒起算)
	//       该节气的月份   (int)(n/2+1)
	//-----------------------------------------------------
	public static function getTermYN($y, $n)
	{
		//15°对性的弧度
		$_QUOTIETY = 3.1415926535897932 * 15.0 / 180.0;
		// 一个回归年 365.242 天
		// y年第n个节气距离(1900-1-0)的天数
		$offday = floor((365.242 * ($y - 1900) + 15.22 * $n  - 1.9 * sin($_QUOTIETY * $n) + 6.2));
		$lu = new Lunar;
		$lu->getOffsetDate($offday);
		return $lu->day;
	}

	//-----------------------------------------------------
	//   功能: 获取 y 年 m 月 d 日 的 节气字符串
	//        如果不是节气日, 返回 NULL
	//
	//   参数: $y:  年号, 如 2004
	//         $m: 月份, 1 ~ 12
	//         $d:   日期, 1 ~ 31
	//----------------------------------------------------
	public static function getTermName($y, $m, $d)
	{
		$szLunarJieqi = array( // 0 ~ 23 ,以 0 小寒 起算
					"小寒",
					"大寒", // 一月的节气
					"立春",
					"雨水", // 二月的节气
					"惊蛰",
					"春分",
					"清明",
					"谷雨",
					"立夏",
					"小满",
					"芒种",
					"夏至",
					"小暑",
					"大暑",
					"立秋",
					"处暑",
					"白露",
					"秋分",
					"寒露",
					"霜降",
					"立冬",
					"小雪",
					"大雪",
					"冬至",
				);
		$n = 0;
		if($d < 15)
		{
			$n = ($m - 1) * 2;
		}
		else
		{
			$n = $m * 2 - 1;
		}
		if(self::getTermYN($y, $n) == $d)
		{// 是一个节气
			return $szLunarJieqi[$n];
		}
		return false;
	}
}

?>
