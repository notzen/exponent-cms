{*
 * Copyright (c) 2004-2011 OIC Group, Inc.
 * Written and Designed by Adam Kessler
 *
 * This file is part of Exponent
 *
 * Exponent is free software; you can redistribute
 * it and/or modify it under the terms of the GNU
 * General Public License as published by the Free
 * Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * GPL: http://www.gnu.org/licenses/gpl.txt
 *
 *}

<h2>{'Configure this Module'|gettext}</h2>

{control type="text" name="title" label="Title"|gettext value=$config.title}
{control type="text" name="count" label="Countdown Date"|gettext value=$config.count}
<em>{'NOTE: date must follow this format'|gettext}: 12/31/2020 5:00 AM</em>
{control type="text" name="message" label="Countdown Finish Message"|gettext value=$config.message}
{control type="editor" name="body" label="Message on clock"|gettext value=$config.body}
