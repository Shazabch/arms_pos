<div id="menu" class="ui-widget-content">
<ul>
	<li><a href="/sop">Home</a></li>
	{if $sessioninfo.level>=9999 or ($sessioninfo.privilege.MST_APPROVAL or $sessioninfo.privilege.SOP_FESTIVAL_DATE)}
		<li><a>Administrator</a>
		    <ul>
		        {if $sessioninfo.level>=9999 or $sessioninfo.privilege.MST_APPROVAL}
	    			<li><a href="approval_flow.php">Approval Flow</a></li>
	    		{/if}
	        </ul>
		</li>
	{/if}
	{if $sessioninfo.level>=9999 or $sessioninfo.privilege.SOP_MST_FEST_DATE}
	    <li><a>Master Files</a>
		    <ul>
	    		{if ($sessioninfo.level>=9999 or $sessioninfo.privilege.SOP_MST_FEST_DATE) and file_exists('masterfile_festival_date.php')}
	    			<li><a href="masterfile_festival_date.php">Festival Date</a></li>
	    		{/if}
	    		{if ($sessioninfo.level>=9999 or $sessioninfo.privilege.SOP_MST_USER_GRP) and file_exists('masterfile_user_group.php')}
	    			<li><a href="masterfile_user_group.php">User Group</a></li>
	    		{/if}
	        </ul>
		</li>
	{/if}
    <li><a>Modules</a>
		<ul>
            {if ((sop_check_privilege('SOP_YMP') or sop_check_privilege('SOP_YMP_EDIT')) and file_exists('yearly_marketing_plan.php')) or (sop_check_privilege('SOP_YMP_APPROVAL') and file_exists('yearly_marketing_plan.approval.php'))}
			    <li><a>Yearly Marketing Plan</a>
			        <ul>
					    {if (sop_check_privilege('SOP_YMP') or sop_check_privilege('SOP_YMP_EDIT')) and file_exists('yearly_marketing_plan.php')}
					    	<li><a href="yearly_marketing_plan.php">Yearly Marketing Plan Setup</a></li>
					    {/if}
					    {if sop_check_privilege('SOP_YMP_APPROVAL') and file_exists('yearly_marketing_plan.approval.php')}
					        <li><a href="yearly_marketing_plan.approval.php">Yearly Marketing Plan Approval</a></li>
					    {/if}
					    {if (sop_check_privilege('SOP_YMP') or sop_check_privilege('SOP_YMP_EDIT')) and file_exists('yearly_marketing_plan.php')}
					    	<li><a href="yearly_marketing_plan.calendar.php">Calendar</a></li>
					    {/if}
					</ul>
			    </li>
		    {/if}
		    {*
		    {if ((sop_check_privilege('SOP_YMERP') or sop_check_privilege('SOP_YMERP_EDIT')) and file_exists('yearly_merchandising_plan.php'))}
		        <li><a>Yearly Merchandising Plan</a>
		            <ul>
		                {if ((sop_check_privilege('SOP_YMERP') or sop_check_privilege('SOP_YMERP_EDIT')) and file_exists('yearly_merchandising_plan.php'))}
		        		<li><a href="yearly_merchandising_plan.php">Yearly Merchandising Plan Setup</a></li>
		        		{/if}
		            </ul>
		        </li>
		    {/if}
		    *}
		</ul>
	</li>
	<li><a>Miscellaneous</a>
		<ul>
		    {if file_exists('activities.php')}<li><a href="activities.php">Activities</a></li>{/if}
		    {if file_exists('reminder.php')}<li><a href="reminder.php">Reminder</a></li>{/if}
		    <li><a href="/login.php?logout=1">Logout</a></li>
	    </ul>
	</li>
</ul>
</div>
